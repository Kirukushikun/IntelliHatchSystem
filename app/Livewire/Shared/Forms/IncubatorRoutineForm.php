<?php

namespace App\Livewire\Shared\Forms;

use App\Livewire\Configs\IncubatorRoutineConfig;
use App\Livewire\Components\FormNavigation;
use App\Livewire\Shared\Forms\Traits\TempPhotoManager;
use App\Models\Incubator;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IncubatorRoutineForm extends FormNavigation
{
    use WithFileUploads, TempPhotoManager;
    protected string $shiftKey = 'shift';

    public array $form = [];

    public array $photoUploads = [];

    /** @var array */
    public $hatcheryMen = [];

    /** @var array */
    public $incubators = [];

    /** @var array */
    public $completedIncubators = [];

    public function mount($formType = 'incubator_routine'): void
    {
        $this->form = IncubatorRoutineConfig::defaultFormState();
        parent::mount($formType);

        $this->schedule = $this->scheduleConfig();
        $this->recalculateVisibleSteps();
        
        // Load hatchery men and incubators
        $this->hatcheryMen = User::where('user_type', 1)
            ->where('is_disabled', false)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(function ($user) {
                return [$user->id => $user->first_name . ' ' . $user->last_name];
            })
            ->toArray();

        $this->incubators = Incubator::where('isActive', true)
            ->orderBy('incubatorName')
            ->get()
            ->mapWithKeys(function ($incubator) {
                return [$incubator->id => $incubator->incubatorName];
            })
            ->toArray();

        $this->updateCompletedIncubators();
    }

    public function updatedForm($value, $key): void
    {
        // Clear errors for this field when it’s updated via wire:model.live.
        $this->resetErrorBag("form.{$key}");
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

            // Get the original form values before they're removed from JSON
            $hatcheryMan = $this->form['hatchery_man'] ?? null;
            $incubator = $this->form['incubator'] ?? null;

            $formId = (int) DB::table('forms')->insertGetId([
                'form_type_id' => $formTypeId,
                'form_inputs' => json_encode($formInputs),
                'date_submitted' => now(),
                'uploaded_by' => $hatcheryMan,
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





    protected function formTypeKey(): string
    {
        return 'incubator_routine';
    }

    protected function formTypeName(): string
    {
        return IncubatorRoutineConfig::getFormTypeName();
    }

    protected function baseRules(): array
    {
        $rules = IncubatorRoutineConfig::getRules();

        // Convert rules to "form.*" keys.
        $converted = [];
        foreach ($rules as $key => $rule) {
            $converted["form.{$key}"] = $rule;
        }

        return $converted;
    }

    protected function messages(): array
    {
        return IncubatorRoutineConfig::getMessages();
    }

    public function updatedFormShift($value): void
    {
        $this->updateCompletedIncubators();
        // Reset incubator selection when shift changes
        $this->form['incubator'] = '';
        // When shift changes, reset the form (except shift) and navigation.
        $this->resetFormExceptShift();
        parent::updatedFormShift($value);
    }

    protected function resetFormExceptShift(bool $keepShift = true, bool $clearShift = false, bool $cleanupPhotos = true): void
    {
        $shift = (string) ($this->form['shift'] ?? '');
        $hatcheryMan = $this->form['hatchery_man'] ?? '';
        $incubator = $this->form['incubator'] ?? '';

        foreach (array_keys($this->form) as $key) {
            if ($key === 'shift' || $key === 'hatchery_man' || $key === 'incubator') {
                continue;
            }

            $this->form[$key] = is_array($this->form[$key]) ? [] : '';
        }

        if ($cleanupPhotos) {
            $this->cleanupAllUploadedPhotos();
            $this->dispatch('formReset');
        }

        if ($clearShift) {
            $this->form['shift'] = '';
            $this->form['hatchery_man'] = '';
            $this->form['incubator'] = '';
            return;
        }

        $this->form['shift'] = $keepShift ? $shift : '';
        $this->form['hatchery_man'] = $hatcheryMan;
        $this->form['incubator'] = $incubator;
    }

    protected function updateCompletedIncubators(): void
    {
        if (empty($this->form['shift'])) {
            $this->completedIncubators = [];
            return;
        }

        $today = now()->format('Y-m-d');
        $formTypeName = $this->formTypeName();
        
        // Get form type ID
        $formTypeId = DB::table('form_types')
            ->where('form_name', $formTypeName)
            ->value('id');

        if (!$formTypeId) {
            $this->completedIncubators = [];
            return;
        }

        // Get incubators that already have forms for today's date and current shift
        $completedForms = DB::table('forms')
            ->where('form_type_id', $formTypeId)
            ->whereDate('date_submitted', $today)
            ->whereNotNull('form_inputs')
            ->get();

        $this->completedIncubators = [];
        
        foreach ($completedForms as $form) {
            $formInputs = is_array($form->form_inputs) ? $form->form_inputs : json_decode($form->form_inputs, true);
            if (isset($formInputs['shift']) && $formInputs['shift'] === $this->form['shift']) {
                if (isset($formInputs['incubator'])) {
                    $this->completedIncubators[] = $formInputs['incubator'];
                }
            }
        }
    }

    public function submitForm(): void
    {
        // Validate only visible fields.
        $rules = $this->rulesForVisibleFields();
        $messages = $this->messages();

        try {
            $this->validate($rules, $messages);
            
            // Ensure all pending photos are fully uploaded before proceeding
            if (!$this->ensureAllPhotosUploaded()) {
                $this->dispatch('showToast', message: 'Photo uploads are still in progress. Please wait for all photos to finish uploading before submitting the form.', type: 'error');
                return;
            }
            
            // Finalize photos BEFORE storing form to avoid race condition
            $formId = $this->storeSubmissionAndReturnId($this->formTypeName(), $this->formInputsForStorageWithoutPhotos());
            $this->finalizePhotosForForm($formId);
            
            // Now store form with finalized photo URLs
            DB::table('forms')->where('id', $formId)->update([
                'form_inputs' => json_encode($this->formInputsWithPhotos($this->formInputsForStorageWithoutPhotos())),
                'updated_at' => now(),
            ]);
            
            // Send form data to webhook
            $this->sendFormToWebhook($formId);
            
            // Store success message in session for display after redirect
            session()->flash('success', 'Form submitted successfully!');
            
            // Redirect to clear form data (like blower forms)
            $this->redirect(route('forms.incubator-routine'));
            
            // Keep form data intact for potential re-submission
            // Data will be cleared when redirected to forms page
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Jump to the first errored field.
            $firstKey = array_key_first($e->validator->errors()->messages());
            if ($firstKey) {
                $fieldName = str_replace('form.', '', $firstKey);
                $this->goToStepWithField($fieldName);
            }

            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to submit form. Please try again.', type: 'error');
            // Keep all form data intact on failure so user can retry
            // Only cleanup photos to prevent orphaned files
            $this->cleanupAllUploadedPhotos();
            throw $e;
        }
    }

    /**
     * Build the form inputs array for storage, including photo URLs and N/A for non-scheduled fields.
     */
    protected function formInputsForStorageWithoutPhotos(): array
    {
        $inputs = $this->form;

        // Remove hatchery_man from JSON as it's stored in dedicated column
        // Keep incubator in JSON for machine info extraction
        unset($inputs['hatchery_man']);

        // Add machine_info structure for consistency with blower air forms
        if (isset($inputs['incubator']) && !empty($inputs['incubator'])) {
            $incubatorId = $inputs['incubator'];
            $incubator = DB::table('incubator-machines')
                ->where('id', $incubatorId)
                ->first();
            
            if ($incubator) {
                $inputs['machine_info'] = [
                    'table' => 'incubator-machines',
                    'id' => $incubatorId,
                    'name' => $incubator->incubatorName
                ];
            }
        }

        // Set N/A for fields that are not scheduled/visible for the selected shift
        $visibleFields = $this->getVisibleFieldNames();

        $allFields = array_keys(IncubatorRoutineConfig::defaultFormState());
        foreach ($allFields as $field) {
            if (!in_array($field, $visibleFields, true)) {
                $inputs[$field] = 'N/A';
            }
        }

        return $inputs;
    }


    protected function rulesForVisibleFields(): array
    {
        $rules = $this->baseRules();
        $visible = $this->getVisibleFieldNames();

        // Always keep shift (step 1).
        $visible[] = 'shift';

        $visibleKeys = array_unique(array_map(fn ($f) => "form.{$f}", $visible));

        // Remove rules for non-visible fields.
        foreach (array_keys($rules) as $key) {
            // Handle wildcard array rules like form.incubator_machine_inspected.*
            $baseKey = preg_replace('/\\.\\*$/', '', $key);
            if (!in_array($baseKey, $visibleKeys, true) && !in_array($key, $visibleKeys, true)) {
                unset($rules[$key]);
            }
        }

        return $rules;
    }

    protected function stepFieldMap(): array
    {
        return IncubatorRoutineConfig::stepFieldMap();
    }

    protected function scheduleConfig(): array
    {
        return IncubatorRoutineConfig::schedule();
    }

    /**
     * Send form data to webhook after successful submission
     */
    protected function sendFormToWebhook(int $formId): void
    {
        try {
            $webhookUrl = config('services.webhook.url');
            
            // Check if webhook URL is configured
            if (!$webhookUrl) {
                Log::error('Webhook URL not configured', [
                    'form_id' => $formId,
                    'config_key' => 'services.webhook.url'
                ]);
                return;
            }
            
            // Get form data with relationships
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
            
            // Ensure formInputs is always an array
            $formInputs = (array) $formInputs;
            
            // Extract machine information from form_inputs
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
                    'shift' => $formInputs['shift'] ?? null,
                    'machine_name' => $machineInfo['name'] ?? null,
                    'submitted_by' => $form->uploaded_by ? trim(($form->first_name ?: '') . ' ' . ($form->last_name ?: '')) : null,
                    'date_time' => date('Y-m-d H:i:s', strtotime($form->date_submitted)),
                    'photos' => $this->extractPhotos($formInputs),
                ],
                'timestamp' => now()->toISOString(),
            ];

            Log::info('Sending form to webhook', [
                'form_id' => $formId,
                'webhook_url' => $webhookUrl,
                'payload_size' => strlen(json_encode($payload)),
            ]);

            $response = Http::post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Form sent to webhook successfully', [
                    'form_id' => $formId,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
            } else {
                Log::error('Failed to send form to webhook', [
                    'form_id' => $formId,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                    'payload_sent' => $payload,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception sending form to webhook', [
                'form_id' => $formId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Extract photo URLs from form inputs
     */
    protected function extractPhotos(array $formInputs): array
    {
        $photos = [];
        
        foreach ($formInputs as $key => $value) {
            // Check if the field ends with '_photos' and contains an array of URLs
            if (str_ends_with($key, '_photos') && is_array($value)) {
                foreach ($value as $photoUrl) {
                    if (is_string($photoUrl) && filter_var($photoUrl, FILTER_VALIDATE_URL)) {
                        $photos[] = [
                            'field' => str_replace('_photos', '', $key),
                            'url' => $photoUrl
                        ];
                    }
                }
            }
        }
        
        return $photos;
    }

    /**
     * Extract machine information from form inputs
     */
    protected function extractMachineInfo(array $formInputs): array
    {
        $machineInfo = [
            'table' => null,
            'id' => null,
            'name' => null
        ];

        // Check if incubator information is available
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
        return view('livewire.shared.forms.incubator-routine-form');
    }
}
