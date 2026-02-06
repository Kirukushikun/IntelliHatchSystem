<?php

namespace App\Livewire\Shared\Management\IncubatorManagement;

use Livewire\Component;
use App\Models\Incubator;
use App\Traits\SanitizesInput;

class Create extends Component
{
    use SanitizesInput;
    
    public $incubatorName = '';
    public $showModal = false;

    protected $rules = [
        'incubatorName' => 'required|string|max:255|unique:incubator-machines,incubatorName',
    ];

    protected $messages = [
        'incubatorName.required' => 'Incubator name is required.',
        'incubatorName.unique' => 'An incubator with this name already exists.',
        'incubatorName.max' => 'Incubator name must not exceed 255 characters.',
    ];

    protected $listeners = ['openCreateModal' => 'openModal'];

    public function openModal()
    {
        $this->reset(['incubatorName']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function createIncubator()
    {
        // Sanitize input before validation
        $this->incubatorName = $this->sanitizeInput($this->incubatorName);
        
        $this->validate();

        try {
            Incubator::create([
                'incubatorName' => $this->incubatorName,
                'isActive' => false, // Default to inactive
            ]);

            $incubatorName = $this->incubatorName; // Store name before closing modal
            $this->closeModal();
            $this->dispatch('refreshIncubators');
            $this->dispatch('showToast', message: "{$incubatorName} has been successfully added", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors will be displayed automatically
            // Just re-throw to let Livewire handle validation display
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to add incubator. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['incubatorName']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.shared.management.incubator-management.create-incubator-management');
    }
}
