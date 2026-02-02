<?php

namespace App\Livewire;

use App\Livewire\Configs\IncubatorRoutineConfig;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FormSubmit extends Component
{
    public $formData = [];
    public $formSubmitted = false;
    public $formType = 'incubator_routine'; // Default form type

    protected function getValidationRules()
    {
        switch ($this->formType) {
            case 'incubator_routine':
                return IncubatorRoutineConfig::getRules();
            default:
                return [];
        }
    }

    protected function getValidationMessages()
    {
        switch ($this->formType) {
            case 'incubator_routine':
                return IncubatorRoutineConfig::getMessages();
            default:
                return [];
        }
    }

    protected function getFormTypeName()
    {
        switch ($this->formType) {
            case 'incubator_routine':
                return IncubatorRoutineConfig::getFormTypeName();
            default:
                return 'Unknown Form';
        }
    }

    public function mount($formType = 'incubator_routine')
    {
        $this->formType = $formType;
        $this->formData = request()->all();
    }

    public function submitForm()
    {
        // Get all form data from request
        $allData = request()->all();
        
        // Get validation rules and messages based on form type
        $rules = $this->getValidationRules();
        $messages = $this->getValidationMessages();

        // Validate the form data
        $validator = Validator::make($allData, $rules, $messages);

        if ($validator->fails()) {
            // Collect all validation errors
            $errors = $validator->errors()->all();
            
            // Show general error message
            $this->dispatch('showToast', message: 'Please complete all required fields before submitting.', type: 'error');
            
            // Show specific validation errors
            foreach ($errors as $error) {
                $this->dispatch('showToast', message: $error, type: 'error');
            }
            
            // Find the first field with an error and dispatch event to go to that step
            $firstErrorField = null;
            foreach ($validator->failed() as $field => $failures) {
                $firstErrorField = $field;
                break;
            }
            
            if ($firstErrorField) {
                // Dispatch event to go to the step containing the error field
                $this->dispatch('goToStepWithField', field: $firstErrorField);
            }
            
            return;
        }

        // Additional check for completely empty required fields
        $emptyFields = [];
        foreach ($rules as $field => $rule) {
            if (str_contains($rule, 'required')) {
                $value = $allData[$field] ?? null;
                if (is_array($value)) {
                    if (empty($value)) {
                        $emptyFields[] = $field;
                    }
                } else {
                    if (empty($value) || trim($value) === '') {
                        $emptyFields[] = $field;
                    }
                }
            }
        }

        if (!empty($emptyFields)) {
            $this->dispatch('showToast', message: 'Please fill in all required fields.', type: 'error');
            
            // Dispatch event to go to the first empty field's step
            $this->dispatch('goToStepWithField', field: $emptyFields[0]);
            return;
        }

        try {
            DB::beginTransaction();

            // Get the form type ID
            $formTypeName = $this->getFormTypeName();
            $formTypeId = DB::table('form_types')
                ->where('form_name', $formTypeName)
                ->value('id');

            if (!$formTypeId) {
                throw new \Exception('Form type not found: ' . $formTypeName);
            }

            // Prepare form data for storage (exclude photos and CSRF token)
            $formDataToStore = [];
            foreach ($allData as $key => $value) {
                if ($key !== '_token' && !str_ends_with($key, '_photos')) {
                    $formDataToStore[$key] = $value;
                }
            }

            // Insert the form submission
            DB::table('forms')->insert([
                'form_type_id' => $formTypeId,
                'form_inputs' => json_encode($formDataToStore),
                'date_submitted' => now(),
                'uploaded_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            $this->formSubmitted = true;
            $this->dispatch('showToast', message: $formTypeName . ' submitted successfully!', type: 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showToast', message: 'Failed to submit form. Please try again.', type: 'error');
            
            // Log the error for debugging
            Log::error('Form submission failed: ' . $e->getMessage());
        }
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

    // This component doesn't render visible content, but needs a root tag for Livewire
    public function render()
    {
        return view('livewire.form-submit');
    }
}
