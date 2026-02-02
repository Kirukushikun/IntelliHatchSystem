<?php

namespace App\Livewire;

use Carbon\Carbon;

abstract class FormWizard extends FormSubmit
{
    public array $schedule = [];

    public int $currentStep = 1;

    /** @var int[] */
    public array $visibleStepIds = [1];

    /**
     * If your form uses a shift selector to unlock scheduled steps,
     * keep this as 'shift' and store it at form['shift'].
     */
    protected string $shiftKey = 'shift';

    /**
     * Child components must provide the schedule config.
     */
    abstract protected function scheduleConfig(): array;

    /**
     * Child components must map step numbers to their field names.
     */
    abstract protected function stepFieldMap(): array;

    public function mount($formType = 'incubator_routine'): void
    {
        parent::mount($formType);

        $this->schedule = $this->scheduleConfig();
        $this->recalculateVisibleSteps();
    }

    public function updatedFormShift($value): void
    {
        // Default behavior: when shift changes, reset to step 1 and recalc.
        // Child component may override reset behavior if needed.
        $this->currentStep = 1;
        $this->recalculateVisibleSteps();
    }

    public function nextStep(): void
    {
        if (!$this->canProceed()) {
            return;
        }

        $idx = array_search($this->currentStep, $this->visibleStepIds, true);
        if ($idx === false) {
            $this->currentStep = 1;
            return;
        }

        if ($idx < count($this->visibleStepIds) - 1) {
            $this->currentStep = $this->visibleStepIds[$idx + 1];
        }
    }

    public function previousStep(): void
    {
        $idx = array_search($this->currentStep, $this->visibleStepIds, true);
        if ($idx === false) {
            $this->currentStep = 1;
            return;
        }

        if ($idx > 0) {
            $this->currentStep = $this->visibleStepIds[$idx - 1];
        }
    }

    public function canProceed(): bool
    {
        if (!empty($this->schedule) && $this->currentStep === 1) {
            return (string) ($this->form[$this->shiftKey] ?? '') !== '';
        }

        return true;
    }

    public function isLastVisibleStep(): bool
    {
        // If schedule is used and shift isn't selected, step 1 should not be treated as last.
        if (!empty($this->schedule) && (string) ($this->form[$this->shiftKey] ?? '') === '') {
            return false;
        }

        return $this->currentStep === (int) end($this->visibleStepIds);
    }

    public function showProgress(): bool
    {
        return (string) ($this->form[$this->shiftKey] ?? '') !== '' && count($this->visibleStepIds) > 1;
    }

    public function isFieldVisible(string $field): bool
    {
        $daily = $this->schedule['_daily'] ?? [];
        if (in_array($field, $daily, true)) {
            return true;
        }

        $allowed = $this->getAllowedFieldsForCurrentShift();
        return in_array($field, $allowed, true);
    }

    protected function goToStepWithField(string $field): void
    {
        foreach ($this->stepFieldMap() as $step => $fields) {
            if (in_array($field, $fields, true)) {
                if (in_array((int) $step, $this->visibleStepIds, true)) {
                    $this->currentStep = (int) $step;
                }
                break;
            }
        }
    }

    protected function recalculateVisibleSteps(): void
    {
        $visible = [1];

        if ((string) ($this->form[$this->shiftKey] ?? '') === '') {
            $this->visibleStepIds = $visible;
            return;
        }

        $allowed = array_merge(($this->schedule['_daily'] ?? []), $this->getAllowedFieldsForCurrentShift());
        $allowed = array_unique($allowed);

        foreach ($this->stepFieldMap() as $step => $fields) {
            if ((int) $step === 1) {
                continue;
            }

            $hasAny = false;
            foreach ($fields as $field) {
                if (in_array($field, $allowed, true)) {
                    $hasAny = true;
                    break;
                }
            }

            if ($hasAny) {
                $visible[] = (int) $step;
            }
        }

        $this->visibleStepIds = array_values(array_unique($visible));
        sort($this->visibleStepIds);

        if (!in_array($this->currentStep, $this->visibleStepIds, true)) {
            $this->currentStep = 1;
        }
    }

    protected function getAllowedFieldsForCurrentShift(): array
    {
        $shift = (string) ($this->form[$this->shiftKey] ?? '');
        if ($shift === '') {
            return [];
        }

        $day = Carbon::now('Asia/Manila')->format('l');
        $key = "{$day}-{$shift}";

        return $this->schedule[$key] ?? [];
    }

    protected function getVisibleFieldNames(): array
    {
        $visible = [];

        foreach (($this->schedule['_daily'] ?? []) as $field) {
            $visible[] = $field;
        }

        foreach ($this->getAllowedFieldsForCurrentShift() as $field) {
            $visible[] = $field;
        }

        return array_values(array_unique($visible));
    }
}
