<?php

namespace App\Livewire;

use App\Livewire\Configs\IncubatorRoutineConfig;
use App\Livewire\FormWizard;
use Illuminate\Support\Facades\Validator;

class IncubatorRoutineForm extends FormWizard
{
    protected string $shiftKey = 'shift';

    public array $form = [];

    /** @var int[] */
    public array $incubatorMachineInspectedOptions = [1,2,3,4,5,6,7,8,9,10];

    public function mount($formType = 'incubator_routine'): void
    {
        $this->form = IncubatorRoutineConfig::defaultFormState();
        parent::mount($formType);
    }

    public function updatedForm($value, $key): void
    {
        // Clear errors for this field when it’s updated via wire:model.live.
        $this->resetErrorBag("form.{$key}");
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

            $this->resetFormExceptShift(false, true);
            $this->currentStep = 1;
            $this->recalculateVisibleSteps();
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to submit form. Please try again.', type: 'error');
            throw $e;
        }
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

    protected function formInputsForStorage(): array
    {
        $data = $this->form;

        // Exclude empty strings for cleaner JSON (optional)
        foreach ($data as $k => $v) {
            if (is_string($v) && trim($v) === '') {
                $data[$k] = '';
            }
        }

        return $data;
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
