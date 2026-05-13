<?php

namespace App\Livewire\Shared\Forms;

use App\Livewire\Configs\HatcherMachineAccuracyConfig;
use App\Livewire\Components\FormNavigation;
use App\Livewire\Shared\Forms\Traits\TempPhotoManager;
use App\Models\Hatcher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;

class HatcherMachineAccuracyForm extends FormNavigation
{
    use WithFileUploads, TempPhotoManager;

    public array $form = [];

    public array $photoUploads = [];

    public bool $formSubmitted = false;

    protected bool $disableShiftLogic = true;

    /** @var array */
    public $hatcheryMen = [];

    public function mount($formType = 'hatcher_machine_accuracy'): void
    {
        $this->form = HatcherMachineAccuracyConfig::defaultFormState();
        $this->form['date_submitted'] = now()->format('Y-m-d');

        parent::mount($formType);
        $this->schedule = $this->scheduleConfig();
        $this->recalculateVisibleSteps();

        $activeHatchers = Hatcher::where('isActive', true)
            ->orderByRaw('LENGTH(hatcherName), hatcherName')
            ->get();

        $this->form['hatchers'] = $activeHatchers->map(fn ($h) => [
            'id'                       => $h->id,
            'name'                     => $h->hatcherName,
            'set_point_temp'           => '',
            'display_temp'             => '',
            'calibrator'               => '',
            'humidity_set_point'       => '',
            'humidity_machine_reading' => '',
        ])->toArray();

        $this->hatcheryMen = $this->loadPersonnelByTags();
        $this->form['hatchery_man'] = $this->initPersonnelField();
    }

    public function updated($name, $value): void
    {
        if (!is_string($name) || !str_starts_with($name, 'photoUploads.')) {
            return;
        }

        $photoKey = substr($name, strlen('photoUploads.'));
        $photoKey = explode('.', $photoKey)[0];

        $files = $this->photoUploads[$photoKey] ?? [];
        if (!is_array($files) || empty($files)) {
            return;
        }

        $this->handleTempPhotoUpload($photoKey, $files, $this->formTypeKey());
    }

    protected function formTypeKey(): string
    {
        return 'hatcher_machine_accuracy';
    }

    protected function scheduleConfig(): array
    {
        return HatcherMachineAccuracyConfig::schedule();
    }

    protected function stepFieldMap(): array
    {
        return HatcherMachineAccuracyConfig::stepFieldMap();
    }

    protected function formTypeName(): string
    {
        return HatcherMachineAccuracyConfig::getFormTypeName();
    }

    protected function messages(): array
    {
        return HatcherMachineAccuracyConfig::getMessages();
    }

    public function submitForm()
    {
        $this->formSubmitted = true;

        try {
            $this->validate(HatcherMachineAccuracyConfig::getRules(), $this->messages());

            if (!$this->ensureAllPhotosUploaded()) {
                $this->dispatch('showToast', message: 'Photo uploads are still in progress. Please wait for all photos to finish uploading before submitting the form.', type: 'error');
                return;
            }

            $formId = $this->storeSubmissionAndReturnId($this->formTypeName(), $this->formInputsForStorageWithoutPhotos());
            $this->finalizePhotosForForm($formId);

            DB::table('forms')->where('id', $formId)->update([
                'form_inputs' => json_encode($this->formInputsWithPhotos($this->formInputsForStorageWithoutPhotos())),
                'updated_at'  => now(),
            ]);

            $this->sendFormToWebhook($formId);

            session()->flash('success', 'Form submitted successfully!');

            return redirect()->route('forms.hatcher-machine-accuracy');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstKey = array_key_first($e->validator->errors()->messages());
            if ($firstKey) {
                $fieldName = str_replace('form.', '', $firstKey);
                $baseField = explode('.', $fieldName)[0];
                $this->goToStepWithField($baseField);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Form submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatch('showToast', message: 'Failed to submit form. Please try again.', type: 'error');
        }
    }

    protected function storeSubmissionAndReturnId(string $formTypeName, array $formInputs): int
    {
        DB::beginTransaction();

        try {
            $formTypeId = DB::table('form_types')
                ->where('form_name', $formTypeName)
                ->value('id');

            if (!$formTypeId) {
                throw new \Exception('Form type not found: ' . $formTypeName);
            }

            $formId = (int) DB::table('forms')->insertGetId([
                'form_type_id'   => $formTypeId,
                'form_inputs'    => json_encode($formInputs),
                'date_submitted' => now(),
                'uploaded_by'    => Auth::id(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            DB::commit();

            return $formId;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function formInputsForStorageWithoutPhotos(): array
    {
        $inputs = $this->form;

        $inputs['hatchery_man'] = $this->resolvePersonnelNames($this->form['hatchery_man'] ?? [], $this->hatcheryMen);

        $hatchers = [];
        foreach ($this->form['hatchers'] as $entry) {
            $hatcher = DB::table('hatcher-machines')
                ->where('id', $entry['id'])
                ->first();

            $hatchers[] = [
                'machine_info' => [
                    'table' => 'hatcher-machines',
                    'id'    => $entry['id'],
                    'name'  => $hatcher ? $hatcher->hatcherName : ($entry['name'] ?? 'Unknown'),
                ],
                'set_point_temp'           => $entry['set_point_temp'],
                'display_temp'             => $entry['display_temp'],
                'calibrator'               => $entry['calibrator'],
                'humidity_set_point'       => $entry['humidity_set_point'],
                'humidity_machine_reading' => $entry['humidity_machine_reading'],
            ];
        }

        $inputs['hatchers'] = $hatchers;

        return $inputs;
    }

    protected function formInputsWithPhotos(array $baseInputs): array
    {
        foreach ($this->uploadedPhotoUrls as $photoKey => $urls) {
            if (str_starts_with($photoKey, 'accuracy_photos_')) {
                $hatcherId = (int) str_replace('accuracy_photos_', '', $photoKey);

                foreach ($baseInputs['hatchers'] as $index => &$entry) {
                    if ((int) ($entry['machine_info']['id'] ?? 0) === $hatcherId) {
                        $entry['accuracy_photos'] = $urls;
                        break;
                    }
                }
                unset($entry);
            } else {
                if (!empty($urls)) {
                    $baseInputs[$photoKey] = $urls;
                }
            }
        }

        foreach ($baseInputs['hatchers'] as &$entry) {
            if (!isset($entry['accuracy_photos'])) {
                $entry['accuracy_photos'] = [];
            }
        }
        unset($entry);

        return $baseInputs;
    }

    protected function sendFormToWebhook(int $formId): void
    {
        try {
            $webhookUrl = config('services.webhook.url');

            if (!$webhookUrl) {
                return;
            }

            $form = DB::table('forms')
                ->select('forms.*', 'form_types.form_name as form_type_name', 'users.first_name', 'users.last_name')
                ->leftJoin('form_types', 'forms.form_type_id', '=', 'form_types.id')
                ->leftJoin('users', 'forms.uploaded_by', '=', 'users.id')
                ->where('forms.id', $formId)
                ->first();

            if (!$form) {
                return;
            }

            $formInputs = is_array($form->form_inputs)
                ? $form->form_inputs
                : json_decode($form->form_inputs, true);
            $formInputs = (array) $formInputs;

            $hatchers = $formInputs['hatchers'] ?? [];
            $machineNames = collect($hatchers)
                ->pluck('machine_info.name')
                ->filter()
                ->implode(', ');

            $payload = [
                'form' => [
                    'form_id'   => $form->id,
                    'form_name' => $form->form_type_name ?: 'Unknown Form Type',
                ],
                'records'        => $formInputs,
                'date_submitted' => date('Y-m-d H:i:s', strtotime($form->date_submitted)),
                'uploaded_by'    => $form->uploaded_by ? [
                    'id'   => $form->uploaded_by,
                    'name' => trim(($form->first_name ?: '') . ' ' . ($form->last_name ?: '')) ?: 'Unknown User',
                ] : null,
                'machines' => collect($hatchers)->map(fn ($h) => $h['machine_info'] ?? null)->filter()->values()->toArray(),
                'message'  => [
                    'form_name'     => $form->form_type_name ?: 'Unknown Form Type',
                    'machine_name'  => $machineNames ?: null,
                    'submitted_by'  => $form->uploaded_by ? trim(($form->first_name ?: '') . ' ' . ($form->last_name ?: '')) : null,
                    'date_time'     => date('Y-m-d H:i:s', strtotime($form->date_submitted)),
                    'photos'        => [],
                    'shift'         => $formInputs['shift'] ?? 'N/A',
                    'hatcher_count' => count($hatchers),
                ],
                'timestamp' => now()->toISOString(),
            ];

            Http::post($webhookUrl, $payload);
        } catch (\Exception $e) {
            Log::error('Exception occurred while sending form to webhook', [
                'form_id' => $formId,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.shared.forms.hatcher-machine-accuracy-form');
    }
}
