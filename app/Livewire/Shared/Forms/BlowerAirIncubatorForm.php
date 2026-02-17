<?php

namespace App\Livewire\Shared\Forms;

use App\Livewire\Configs\BlowerAirIncubatorConfig;
use App\Livewire\Components\FormNavigation;
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
    use WithFileUploads;

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

    /** @var array<string, string[]> Track uploaded photo URLs per field */
    public array $uploadedPhotoUrls = [];

    /** @var array<string, int[]> Track uploaded photo IDs per field */
    public array $uploadedPhotoIds = [];

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

        $this->validateOnly("photoUploads.{$photoKey}.*", [
            "photoUploads.{$photoKey}.*" => ['image', 'max:1024'],
        ]);

        $formType = $this->formTypeKey();
        $timestamp = now()->format('Ymd_His');

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            $uuid = Str::uuid()->getHex();

            $ext = method_exists($file, 'getClientOriginalExtension') ? $file->getClientOriginalExtension() : 'jpg';

            $originalName = method_exists($file, 'getClientOriginalName') ? (string) $file->getClientOriginalName() : 'photo';
            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
            $safePhotoName = Str::slug($baseName);
            if ($safePhotoName === '') {
                $safePhotoName = 'photo';
            }

            $filename = "{$timestamp}_{$formType}_FORMID_{$photoKey}_{$safePhotoName}-{$uuid}.{$ext}";
            $path = $file->storeAs('forms', $filename, 'public');

            if (!$path) {
                continue;
            }

            $url = $this->absolutePublicUrlFromDiskPath($path);

            $photoId = (int) DB::table('photos')->insertGetId([
                'public_path' => $url,
                'disk' => 'public',
                'uploaded_by' => Auth::id() ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->uploadedPhotoUrls[$photoKey][] = $url;
            $this->uploadedPhotoIds[$photoKey][] = $photoId;

            $this->dispatch('photoStored', photoKey: $photoKey, photoId: $photoId, url: $url);
        }

        $this->photoUploads[$photoKey] = [];
    }

    protected function formTypeKey(): string
    {
        return 'blower_air_incubator';
    }

    protected function absolutePublicUrlFromDiskPath(string $diskPath): string
    {
        return url(Storage::url($diskPath));
    }

    protected function diskPathFromPublicUrl(string $publicUrl): string
    {
        $path = parse_url($publicUrl, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return ltrim(str_replace('/storage/', '', $publicUrl), '/');
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            return substr($path, strlen('storage/'));
        }

        return $path;
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
            $formId = $this->storeSubmissionAndReturnId($this->formTypeName(), $this->formInputsForStorageWithoutPhotos());
            $this->finalizeUploadedPhotosForForm($formId);
            DB::table('forms')->where('id', $formId)->update([
                'form_inputs' => json_encode($this->formInputsForStorageWithPhotoUrls()),
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
     * Finalize uploaded photos for a specific form
     */
    protected function finalizeUploadedPhotosForForm(int $formId): void
    {
        foreach ($this->uploadedPhotoIds as $photoKey => $ids) {
            foreach ($ids as $i => $photoId) {
                $photo = DB::table('photos')->where('id', (int) $photoId)->first();
                if (!$photo || !isset($photo->public_path)) {
                    continue;
                }

                $currentUrl = (string) $photo->public_path;
                $relativePath = $this->diskPathFromPublicUrl($currentUrl);
                if ($relativePath === '' || !str_contains($relativePath, '_FORMID_')) {
                    continue;
                }

                $newRelativePath = str_replace('_FORMID_', "_{$formId}_", $relativePath);

                $moved = Storage::disk('public')->move($relativePath, $newRelativePath);
                if (!$moved) {
                    continue;
                }

                $newUrl = $this->absolutePublicUrlFromDiskPath($newRelativePath);

                DB::table('photos')->where('id', (int) $photoId)->update([
                    'public_path' => $newUrl,
                    'updated_at' => now(),
                ]);

                if (isset($this->uploadedPhotoUrls[$photoKey][$i])) {
                    $this->uploadedPhotoUrls[$photoKey][$i] = $newUrl;
                }
            }
        }
    }

    public function deleteUploadedPhoto(string $photoKey, int $photoId): void
    {
        $ids = $this->uploadedPhotoIds[$photoKey] ?? [];
        if (!in_array($photoId, $ids, true)) {
            return;
        }

        $photo = DB::table('photos')->where('id', $photoId)->first();
        if ($photo && isset($photo->public_path)) {
            $relativePath = $this->diskPathFromPublicUrl((string) $photo->public_path);
            if ($relativePath !== '') {
                Storage::disk('public')->delete($relativePath);
            }
        }

        DB::table('photos')->where('id', $photoId)->delete();

        $this->uploadedPhotoIds[$photoKey] = array_values(array_filter(
            $this->uploadedPhotoIds[$photoKey] ?? [],
            fn ($id) => (int) $id !== $photoId
        ));

        $this->uploadedPhotoUrls[$photoKey] = array_values(array_filter(
            $this->uploadedPhotoUrls[$photoKey] ?? [],
            function ($url) use ($photo, $photoId) {
                if ($photo && isset($photo->public_path)) {
                    return (string) $url !== (string) $photo->public_path;
                }

                return true;
            }
        ));
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
     * Build form inputs array with photo URLs included
     */
    protected function formInputsForStorageWithPhotoUrls(): array
    {
        $inputs = $this->formInputsForStorageWithoutPhotos();

        foreach ($this->uploadedPhotoUrls as $photoKey => $urls) {
            if (!empty($urls)) {
                $inputs[$photoKey] = $urls;
            }
        }

        return $inputs;
    }

    /**
     * Send form data to webhook after successful submission
     */
    protected function sendFormToWebhook(int $formId): void
    {
        try {
            $webhookUrl = env('WEBHOOK_URL');
            
            // Check if webhook URL is configured
            if (!$webhookUrl) {
                Log::error('Webhook URL not configured', [
                    'form_id' => $formId,
                    'env_variable' => 'WEBHOOK_URL'
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
