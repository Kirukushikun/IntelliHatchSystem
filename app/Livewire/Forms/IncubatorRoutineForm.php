<?php

namespace App\Livewire\Forms;

use App\Livewire\Configs\IncubatorRoutineConfig;
use App\Livewire\FormNavigation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IncubatorRoutineForm extends FormNavigation
{
    use WithFileUploads;
    protected string $shiftKey = 'shift';

    public array $form = [];

    public array $photoUploads = [];

    public array $uploadedPhotoIds = [];

    /** @var int[] */
    public array $incubatorMachineInspectedOptions = [1,2,3,4,5,6,7,8,9,10];

    /** @var array<string, string[]> Track uploaded photo URLs per field */
    public array $uploadedPhotoUrls = [];

    public function mount($formType = 'incubator_routine'): void
    {
        $this->form = IncubatorRoutineConfig::defaultFormState();
        parent::mount($formType);

        $this->schedule = $this->scheduleConfig();
        $this->recalculateVisibleSteps();
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

        $this->validateOnly("photoUploads.{$photoKey}.*", [
            "photoUploads.{$photoKey}.*" => ['image', 'max:15360'],
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
                'uploaded_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->uploadedPhotoUrls[$photoKey][] = $url;
            $this->uploadedPhotoIds[$photoKey][] = $photoId;

            $this->dispatch('photoStored', photoKey: $photoKey, photoId: $photoId, url: $url);
        }

        $this->photoUploads[$photoKey] = [];
    }

    protected function storeSubmission(string $formTypeName, array $formInputs): void
    {
        DB::beginTransaction();

        try {
            $formTypeId = DB::table('form_types')
                ->where('form_name', $formTypeName)
                ->value('id');

            if (!$formTypeId) {
                throw new \Exception('Form type not found: ' . $formTypeName);
            }

            DB::table('forms')->insert([
                'form_type_id' => $formTypeId,
                'form_inputs' => json_encode($formInputs),
                'date_submitted' => now(),
                'uploaded_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
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

    /**
     * Cleanup all uploaded photos when component is destroyed or form reset.
     */
    protected function cleanupAllUploadedPhotos(): void
    {
        foreach ($this->uploadedPhotoUrls as $field => $urls) {
            foreach ($urls as $url) {
                $relativePath = $this->diskPathFromPublicUrl($url);
                Storage::disk('public')->delete($relativePath);
            }
        }

        $allIds = [];
        foreach ($this->uploadedPhotoIds as $field => $ids) {
            foreach ($ids as $id) {
                $allIds[] = $id;
            }
        }

        if (!empty($allIds)) {
            DB::table('photos')->whereIn('id', $allIds)->delete();
        }

        $this->uploadedPhotoUrls = [];
        $this->uploadedPhotoIds = [];
        $this->photoUploads = [];
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
        $messages = IncubatorRoutineConfig::getMessages();

        // Keep global rule messages (e.g. 'required', 'in') as-is.
        // Prefix field-specific messages to match nested validation keys (e.g. 'form.shift.required').
        $converted = [];
        foreach ($messages as $key => $message) {
            if (str_contains($key, '.')) {
                $converted["form.{$key}"] = $message;
                continue;
            }

            $converted[$key] = $message;
        }

        return $converted;
    }

    public function updatedFormShift($value): void
    {
        // When shift changes, reset the form (except shift) and navigation.
        $this->resetFormExceptShift();
        parent::updatedFormShift($value);
    }

    protected function resetFormExceptShift(bool $keepShift = true, bool $clearShift = false, bool $cleanupPhotos = true): void
    {
        $shift = (string) ($this->form['shift'] ?? '');

        foreach (array_keys($this->form) as $key) {
            if ($key === 'shift') {
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
            return;
        }

        $this->form['shift'] = $keepShift ? $shift : '';
    }

    public function submitForm(): void
    {
        // Validate only visible fields.
        $rules = $this->rulesForVisibleFields();
        $messages = $this->messages();

        try {
            $this->validate($rules, $messages);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Jump to the first errored field.
            $firstKey = array_key_first($e->validator->errors()->messages());
            if ($firstKey) {
                $fieldName = str_replace('form.', '', $firstKey);
                $this->goToStepWithField($fieldName);
            }

            throw $e;
        }

        try {
            $formId = $this->storeSubmissionAndReturnId($this->formTypeName(), $this->formInputsForStorageWithoutPhotos());
            $this->finalizeUploadedPhotosForForm($formId);
            DB::table('forms')->where('id', $formId)->update([
                'form_inputs' => json_encode($this->formInputsForStorageWithPhotoUrls()),
                'updated_at' => now(),
            ]);
            $this->dispatch('showToast', message: 'Form submitted successfully!', type: 'success');
            $this->dispatch('formSubmitted');

            // Do NOT cleanup photos on successful submit; they are now stored in DB
            $this->resetFormExceptShift(false, true, false);
            $this->uploadedPhotoUrls = [];
            $this->uploadedPhotoIds = [];
            $this->photoUploads = [];
            $this->currentStep = 1;
            $this->recalculateVisibleSteps();
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to submit form. Please try again.', type: 'error');
            // Cleanup photos on submit failure
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

    public function render()
    {
        return view('livewire.incubator-routine-form');
    }
}
