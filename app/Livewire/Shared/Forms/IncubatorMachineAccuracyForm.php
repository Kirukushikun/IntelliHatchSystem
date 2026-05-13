<?php

namespace App\Livewire\Shared\Forms;

use App\Livewire\Configs\IncubatorMachineAccuracyConfig;
use App\Livewire\Components\FormNavigation;
use App\Livewire\Shared\Forms\Traits\TempPhotoManager;
use App\Models\Incubator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;

class IncubatorMachineAccuracyForm extends FormNavigation
{
    use WithFileUploads, TempPhotoManager;

    public array $form = [];

    public array $photoUploads = [];

    public bool $formSubmitted = false;

    protected bool $disableShiftLogic = true;

    /** @var array */
    public $hatcheryMen = [];

    public function mount($formType = 'incubator_machine_accuracy'): void
    {
        $this->form = IncubatorMachineAccuracyConfig::defaultFormState();
        $this->form['date_submitted'] = now()->format('Y-m-d');

        parent::mount($formType);
        $this->schedule = $this->scheduleConfig();
        $this->recalculateVisibleSteps();

        $activeIncubators = Incubator::where('isActive', true)
            ->orderByRaw('LENGTH(incubatorName), incubatorName')
            ->get();

        $this->form['incubators'] = $activeIncubators->map(fn ($m) => [
            'id'           => $m->id,
            'name'         => $m->incubatorName,
            'display_temp' => '',
            'calibrator'   => '',
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
        return 'incubator_machine_accuracy';
    }

    protected function scheduleConfig(): array
    {
        return IncubatorMachineAccuracyConfig::schedule();
    }

    protected function stepFieldMap(): array
    {
        return IncubatorMachineAccuracyConfig::stepFieldMap();
    }

    protected function formTypeName(): string
    {
        return IncubatorMachineAccuracyConfig::getFormTypeName();
    }

    protected function messages(): array
    {
        return IncubatorMachineAccuracyConfig::getMessages();
    }

    public function submitForm()
    {
        $this->formSubmitted = true;

        try {
            $this->validate(IncubatorMachineAccuracyConfig::getRules(), $this->messages());

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

            return redirect()->route('forms.incubator-machine-accuracy');
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

        $incubators = [];
        foreach ($this->form['incubators'] as $entry) {
            $incubator = DB::table('incubator-machines')
                ->where('id', $entry['id'])
                ->first();

            $incubators[] = [
                'machine_info' => [
                    'table' => 'incubator-machines',
                    'id'    => $entry['id'],
                    'name'  => $incubator ? $incubator->incubatorName : ($entry['name'] ?? 'Unknown'),
                ],
                'display_temp' => $entry['display_temp'],
                'calibrator'   => $entry['calibrator'],
            ];
        }

        $inputs['incubators'] = $incubators;

        return $inputs;
    }

    protected function formInputsWithPhotos(array $baseInputs): array
    {
        foreach ($this->uploadedPhotoUrls as $photoKey => $urls) {
            if (str_starts_with($photoKey, 'accuracy_photos_')) {
                $incubatorId = (int) str_replace('accuracy_photos_', '', $photoKey);

                foreach ($baseInputs['incubators'] as $index => &$entry) {
                    if ((int) ($entry['machine_info']['id'] ?? 0) === $incubatorId) {
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

        foreach ($baseInputs['incubators'] as &$entry) {
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

            $incubators = $formInputs['incubators'] ?? [];
            $machineNames = collect($incubators)
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
                'machines' => collect($incubators)->map(fn ($inc) => $inc['machine_info'] ?? null)->filter()->values()->toArray(),
                'message'  => [
                    'form_name'       => $form->form_type_name ?: 'Unknown Form Type',
                    'machine_name'    => $machineNames ?: null,
                    'submitted_by'    => $form->uploaded_by ? trim(($form->first_name ?: '') . ' ' . ($form->last_name ?: '')) : null,
                    'date_time'       => date('Y-m-d H:i:s', strtotime($form->date_submitted)),
                    'photos'          => [],
                    'shift'           => $formInputs['shift'] ?? 'N/A',
                    'incubator_count' => count($incubators),
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
        return view('livewire.shared.forms.incubator-machine-accuracy-form');
    }
}
