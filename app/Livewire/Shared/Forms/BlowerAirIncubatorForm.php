<?php

namespace App\Livewire\Shared\Forms;

use App\Livewire\Configs\BlowerAirIncubatorConfig;
use App\Livewire\Components\FormNavigation;
use App\Livewire\Shared\Forms\Traits\TempPhotoManager;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Incubator;
use App\Models\User;

class BlowerAirIncubatorForm extends FormNavigation
{
    use WithFileUploads, TempPhotoManager;

    public array $form = [];

    public array $photoUploads = [];

    public bool $formSubmitted = false;

    // Disable shift-based navigation for this form
    protected bool $disableShiftLogic = true;

    /**
     * @var array
     */
    public $incubators = [];

    /** @var array */
    public $hatcheryMen = [];

    /** @var array */
    public $completedIncubators = [];

    /** @var int|null Store uploaded_by user ID from name field */
    public ?int $uploadedBy = null;

    public function mount($formType = 'blower_air_incubator'): void
    {
        $this->form = BlowerAirIncubatorConfig::defaultFormState();
        
        parent::mount($formType);
        $this->schedule = $this->scheduleConfig();
        $this->recalculateVisibleSteps();
        
        // Load incubators
        $this->incubators = Incubator::where('isActive', true)
            ->orderBy('incubatorName')
            ->get()
            ->mapWithKeys(function ($incubator) {
                return [$incubator->id => $incubator->incubatorName];
            })
            ->toArray();
        
        // Load hatchery men
        $this->hatcheryMen = User::where('user_type', 1)
            ->where('is_disabled', false)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(function ($user) {
                return [$user->id => $user->first_name . ' ' . $user->last_name];
            })
            ->toArray();
        
        // Update completed incubators for today
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
        return 'blower_air_incubator';
    }


    protected function scheduleConfig(): array
    {
        return BlowerAirIncubatorConfig::schedule();
    }

    protected function messages(): array
    {
        return BlowerAirIncubatorConfig::getMessages();
    }

    protected function stepFieldMap(): array
    {
        return BlowerAirIncubatorConfig::stepFieldMap();
    }

    protected function formTypeName(): string
    {
        return BlowerAirIncubatorConfig::getFormTypeName();
    }

    public function submitForm()
    {
        $this->formSubmitted = true;
        
        $this->validate(BlowerAirIncubatorConfig::getRules());

        try {
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
            
            return redirect()->route('forms.blower-air-incubator');
        } catch (\Exception $e) {
            Log::error('Form submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('showToast', message: 'Failed to submit form. Please try again.', type: 'error');
        }
    }

    /**
     * Store form submission and return form ID
     */
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

            // Get original form values before they're removed from JSON
            $hatcheryMan = $this->form['hatchery_man'] ?? null;
            $incubator = $this->form['incubator'] ?? null;

            $formId = (int) DB::table('forms')->insertGetId([
                'form_type_id' => $formTypeId,
                'form_inputs' => json_encode($formInputs),
                'date_submitted' => now(),
                'uploaded_by' => $this->uploadedBy ?: $hatcheryMan,
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


    /**
     * Build form inputs array for storage
     */
    protected function formInputsForStorageWithoutPhotos(): array
    {
        $inputs = $this->form;

        // Remove hatchery_man from JSON as it's stored in dedicated column
        // Remove incubator from JSON since it's now in machine_info
        // Photos should stay in form_inputs JSON
        unset($inputs['hatchery_man']);
        unset($inputs['incubator']);

        // Add machine information to JSON for easier extraction
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

    /**
     * Update completed incubators based on day and form type (no shift logic)
     */
    protected function updateCompletedIncubators(): void
    {
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

        // Get incubators that already have forms for today's date and same form type
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
                    'machine_name' => $machineInfo['name'] ?? null,
                    'submitted_by' => $form->uploaded_by ? trim(($form->first_name ?: '') . ' ' . ($form->last_name ?: '')) : null,
                    'date_time' => date('Y-m-d H:i:s', strtotime($form->date_submitted)),
                    'photos' => $this->extractPhotos($formInputs),
                    'shift' => 'N/A',
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
            Log::error('Exception occurred while sending form to webhook', [
                'form_id' => $formId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Extract photos from form inputs
     */
    protected function extractPhotos(array $formInputs): array
    {
        $photos = [];
        
        if (isset($formInputs['photos']) && is_array($formInputs['photos'])) {
            foreach ($formInputs['photos'] as $photo) {
                if (is_string($photo)) {
                    $photos[] = $photo;
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
        // Check if machine information is already stored in JSON
        if (isset($formInputs['machine_info'])) {
            return $formInputs['machine_info'];
        }

        // Fallback to original method if machine_info not found
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
        return view('livewire.shared.forms.blower-air-incubator-form');
    }
}
