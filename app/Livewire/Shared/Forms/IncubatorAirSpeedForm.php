<?php

namespace App\Livewire\Shared\Forms;

use App\Livewire\Configs\IncubatorAirSpeedConfig;
use App\Livewire\Components\FormNavigation;
use App\Livewire\Shared\Forms\Traits\TempPhotoManager;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Incubator;

class IncubatorAirSpeedForm extends FormNavigation
{
    use WithFileUploads, TempPhotoManager;

    public array $form = [];

    public array $photoUploads = [];

    public bool $formSubmitted = false;

    protected bool $disableShiftLogic = true;

    public $incubators = [];

    public $hatcheryMen = [];

    public $completedIncubators = [];

    public ?int $uploadedBy = null;

    public function mount($formType = 'incubator_air_speed'): void
    {
        $this->form = IncubatorAirSpeedConfig::defaultFormState();

        parent::mount($formType);
        $this->schedule = $this->scheduleConfig();
        $this->recalculateVisibleSteps();

        $this->incubators = Incubator::where('isActive', true)
            ->orderByRaw('LENGTH(incubatorName), incubatorName')
            ->get()
            ->mapWithKeys(function ($incubator) {
                return [$incubator->id => $incubator->incubatorName];
            })
            ->toArray();

        $this->hatcheryMen = $this->loadPersonnelByTags();
        $this->form['hatchery_man'] = $this->initPersonnelField();

        $this->updateCompletedIncubators();
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

        $formType = $this->formTypeKey();
        $this->handleTempPhotoUpload($photoKey, $files, $formType);
    }

    protected function formTypeKey(): string
    {
        return 'incubator_air_speed';
    }

    protected function scheduleConfig(): array
    {
        return IncubatorAirSpeedConfig::schedule();
    }

    protected function messages(): array
    {
        return IncubatorAirSpeedConfig::getMessages();
    }

    protected function stepFieldMap(): array
    {
        return IncubatorAirSpeedConfig::stepFieldMap();
    }

    protected function formTypeName(): string
    {
        return IncubatorAirSpeedConfig::getFormTypeName();
    }

    public function submitForm()
    {
        $this->formSubmitted = true;

        $this->validate(IncubatorAirSpeedConfig::getRules());

        try {
            if (!$this->ensureAllPhotosUploaded()) {
                $this->dispatch('showToast', message: 'Photo uploads are still in progress. Please wait for all photos to finish uploading before submitting the form.', type: 'error');
                return;
            }

            $formId = $this->storeSubmissionAndReturnId($this->formTypeName(), $this->formInputsForStorageWithoutPhotos());
            $this->finalizePhotosForForm($formId);

            DB::table('forms')->where('id', $formId)->update([
                'form_inputs' => json_encode($this->formInputsWithPhotos($this->formInputsForStorageWithoutPhotos())),
                'updated_at' => now(),
            ]);

            $this->sendFormToWebhook($formId);

            session()->flash('success', 'Form submitted successfully!');

            return redirect()->route('forms.incubator-air-speed');
        } catch (\Exception $e) {
            Log::error('Form submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
                'form_type_id' => $formTypeId,
                'form_inputs' => json_encode($formInputs),
                'date_submitted' => now(),
                'uploaded_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
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
        unset($inputs['incubator']);

        if (isset($this->form['incubator']) && !empty($this->form['incubator'])) {
            $incubator = DB::table('incubator-machines')
                ->where('id', $this->form['incubator'])
                ->first();

            if ($incubator) {
                $inputs['machine_info'] = [
                    'table' => 'incubator-machines',
                    'id' => $this->form['incubator'],
                    'name' => $incubator->incubatorName
                ];
            }
        }

        return $inputs;
    }

    protected function updateCompletedIncubators(): void
    {
        $today = now()->format('Y-m-d');
        $formTypeName = $this->formTypeName();

        $formTypeId = DB::table('form_types')
            ->where('form_name', $formTypeName)
            ->value('id');

        if (!$formTypeId) {
            $this->completedIncubators = [];
            return;
        }

        $completedForms = DB::table('forms')
            ->where('form_type_id', $formTypeId)
            ->whereDate('date_submitted', $today)
            ->whereNotNull('form_inputs')
            ->get();

        $this->completedIncubators = [];

        foreach ($completedForms as $form) {
            $formInputs = is_array($form->form_inputs) ? $form->form_inputs : json_decode($form->form_inputs, true);
            if (isset($formInputs['incubator'])) {
                $this->completedIncubators[] = $formInputs['incubator'];
            }
        }
    }

    protected function sendFormToWebhook(int $formId): void
    {
        try {
            $webhookUrl = config('services.webhook.url');

            if (!$webhookUrl) {
                Log::error('Webhook URL not configured', [
                    'form_id' => $formId,
                    'config_key' => 'services.webhook.url'
                ]);
                return;
            }

            $form = DB::table('forms')
                ->select('forms.*', 'form_types.form_name as form_type_name', 'users.first_name', 'users.last_name')
                ->leftJoin('form_types', 'forms.form_type_id', '=', 'form_types.id')
                ->leftJoin('users', 'forms.uploaded_by', '=', 'users.id')
                ->where('forms.id', $formId)
                ->first();

            if (!$form) {
                Log::error('Form not found for webhook', ['form_id' => $formId]);
                return;
            }

            $formInputs = is_array($form->form_inputs) ? $form->form_inputs : json_decode($form->form_inputs, true);
            $formInputs = (array) $formInputs;

            $machineInfo = $this->extractMachineInfo($formInputs);

            $payload = [
                'form' => [
                    'form_id' => $form->id,
                    'form_name' => $form->form_type_name ?: 'Unknown Form Type',
                ],
                'records' => $formInputs,
                'date_submitted' => date('Y-m-d H:i:s', strtotime($form->date_submitted)),
                'uploaded_by' => $form->uploaded_by ? [
                    'id' => $form->uploaded_by,
                    'name' => trim(($form->first_name ?: '') . ' ' . ($form->last_name ?: '')) ?: 'Unknown User',
                ] : null,
                'machine' => $machineInfo,
                'message' => [
                    'form_name' => $form->form_type_name ?: 'Unknown Form Type',
                    'machine_name' => $machineInfo['name'] ?? null,
                    'submitted_by' => $form->uploaded_by ? trim(($form->first_name ?: '') . ' ' . ($form->last_name ?: '')) : null,
                    'date_time' => date('Y-m-d H:i:s', strtotime($form->date_submitted)),
                    'photos' => $this->extractPhotos($formInputs),
                    'shift' => 'N/A',
                ],
                'timestamp' => now()->toISOString(),
            ];

            $response = Http::post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Form sent to webhook successfully', [
                    'form_id' => $formId,
                    'response_status' => $response->status(),
                ]);
            } else {
                Log::error('Failed to send form to webhook', [
                    'form_id' => $formId,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while sending form to webhook', [
                'form_id' => $formId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function extractPhotos(array $formInputs): array
    {
        $photos = [];

        $photoFields = [
            'left_baggy_top_photos',
            'left_baggy_middle_photos',
            'left_baggy_bottom_photos',
            'right_baggy_top_photos',
            'right_baggy_middle_photos',
            'right_baggy_bottom_photos',
        ];

        foreach ($photoFields as $field) {
            if (isset($formInputs[$field]) && is_array($formInputs[$field])) {
                foreach ($formInputs[$field] as $photo) {
                    if (is_string($photo)) {
                        $photos[] = $photo;
                    } elseif (is_array($photo) && isset($photo['url'])) {
                        $photos[] = $photo['url'];
                    }
                }
            }
        }

        return $photos;
    }

    protected function extractMachineInfo(array $formInputs): array
    {
        if (isset($formInputs['machine_info'])) {
            return $formInputs['machine_info'];
        }

        $machineInfo = [
            'table' => null,
            'id' => null,
            'name' => null
        ];

        if (isset($formInputs['incubator']) && !empty($formInputs['incubator'])) {
            $incubatorId = $formInputs['incubator'];
            $incubator = DB::table('incubator-machines')
                ->where('id', $incubatorId)
                ->first();

            if ($incubator) {
                $machineInfo = [
                    'table' => 'incubator-machines',
                    'id' => $incubatorId,
                    'name' => $incubator->incubatorName
                ];
            }
        }

        return $machineInfo;
    }

    public function render()
    {
        return view('livewire.shared.forms.incubator-air-speed-form');
    }
}
