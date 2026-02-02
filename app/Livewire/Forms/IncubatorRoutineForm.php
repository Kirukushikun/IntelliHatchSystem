<?php

namespace App\Livewire\Forms;

use App\Livewire\Configs\IncubatorRoutineConfig;
use App\Livewire\FormNavigation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IncubatorRoutineForm extends FormNavigation
{
    use WithFileUploads;
    protected string $shiftKey = 'shift';

    public array $form = [];

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

    /**
     * Process and store uploaded photos for a given field from the request.
     * Returns public URLs array.
     */
    protected function processUploadedPhotosForField(string $field): array
    {
        $key = "{$field}_photos";
        $files = request()->file($key);
        if (!$files) {
            return [];
        }

        $urls = [];
        $prefix = 'forms/' . now()->format('Y/m/d') . '/' . $field;

        foreach ((array) $files as $file) {
            if (!$file instanceof \Illuminate\Http\UploadedFile || !$file->isValid()) {
                continue;
            }

            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($prefix, $filename, 'public');

            if ($path) {
                $url = Storage::url($path);
                $urls[] = $url;
                $this->uploadedPhotoUrls[$field][] = $url;
            }
        }

        return $urls;
    }

    /**
     * Remove a specific uploaded photo for a field.
     */
    protected function removePhotoForField(string $field, string $url): void
    {
        if (isset($this->uploadedPhotoUrls[$field])) {
            $this->uploadedPhotoUrls[$field] = array_values(array_filter(
                $this->uploadedPhotoUrls[$field],
                fn ($u) => $u !== $url
            ));

            // Delete from public disk
            $relativePath = str_replace('/storage/', '', $url);
            Storage::disk('public')->delete($relativePath);
        }
    }

    /**
     * Clear all uploaded photos for a field (used on cancel/reset).
     */
    protected function clearPhotosForField(string $field): void
    {
        if (isset($this->uploadedPhotoUrls[$field])) {
            foreach ($this->uploadedPhotoUrls[$field] as $url) {
                $relativePath = str_replace('/storage/', '', $url);
                Storage::disk('public')->delete($relativePath);
            }
            unset($this->uploadedPhotoUrls[$field]);
        }
    }

    /**
     * Cleanup all uploaded photos when component is destroyed or form reset.
     */
    protected function cleanupAllUploadedPhotos(): void
    {
        foreach ($this->uploadedPhotoUrls as $field => $urls) {
            foreach ($urls as $url) {
                $relativePath = str_replace('/storage/', '', $url);
                Storage::disk('public')->delete($relativePath);
            }
        }
        $this->uploadedPhotoUrls = [];
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

    protected function resetFormExceptShift(bool $keepShift = true, bool $clearShift = false): void
    {
        $shift = (string) ($this->form['shift'] ?? '');

        foreach (array_keys($this->form) as $key) {
            if ($key === 'shift') {
                continue;
            }

            $this->form[$key] = is_array($this->form[$key]) ? [] : '';
        }

        // Cleanup uploaded photos when resetting form
        $this->cleanupAllUploadedPhotos();

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
            $this->storeSubmission($this->formTypeName(), $this->formInputsForStorage());
            $this->dispatch('showToast', message: 'Form submitted successfully!', type: 'success');

            // Do NOT cleanup photos on successful submit; they are now stored in DB JSON
            $this->resetFormExceptShift(false, true);
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
    protected function formInputsForStorage(): array
    {
        $inputs = $this->form;

        // Process and include uploaded photo URLs from request
        $allFields = array_keys(IncubatorRoutineConfig::defaultFormState());
        foreach ($allFields as $field) {
            $photoUrls = $this->processUploadedPhotosForField($field);
            if (!empty($photoUrls)) {
                $inputs["{$field}_photos"] = $photoUrls;
            }
        }

        // Set N/A for fields that are not scheduled/visible for the selected shift
        $visibleFields = $this->getVisibleFieldNames();

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

    public function render()
    {
        return view('livewire.incubator-routine-form');
    }
}
