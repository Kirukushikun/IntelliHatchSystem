<?php

namespace App\Livewire\IncubatorManagement;

use Livewire\Component;
use App\Models\Incubator;

class Edit extends Component
{
    public $incubatorId = '';
    public $incubatorName = '';
    public $showModal = false;
    public $originalIncubatorName;

    protected $rules = [
        'incubatorName' => 'required|string|max:255',
    ];

    protected $messages = [
        'incubatorName.required' => 'Incubator name is required.',
        'incubatorName.max' => 'Incubator name must not exceed 255 characters.',
    ];

    protected $listeners = ['openEditModal' => 'openModal'];

    public function openModal($incubatorId)
    {
        $this->incubatorId = $incubatorId;
        $incubator = Incubator::find($this->incubatorId);
        
        if ($incubator) {
            $this->incubatorName = $incubator->incubatorName;
            $this->originalIncubatorName = $incubator->incubatorName;
            $this->resetValidation();
            $this->showModal = true;
        }
    }

    public function updatedIncubatorName()
    {
        // If the name is the same as original, remove unique validation
        if ($this->incubatorName === $this->originalIncubatorName) {
            $this->rules['incubatorName'] = 'required|string|max:255';
        } else {
            $this->rules['incubatorName'] = 'required|string|max:255|unique:incubator-machines,incubatorName';
        }
    }

    public function updateIncubator()
    {
        $this->updatedIncubatorName(); // Update validation rules
        $this->validate();

        try {
            $incubator = Incubator::findOrFail($this->incubatorId);
            
            $incubator->update([
                'incubatorName' => $this->incubatorName,
            ]);

            $incubatorName = $this->incubatorName; // Store name before closing modal
            $this->closeModal();
            $this->dispatch('refreshIncubators');
            $this->dispatch('showToast', message: "{$incubatorName} has been successfully updated!", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update incubator. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['incubatorName', 'incubatorId', 'originalIncubatorName']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.incubator-management.edit-incubator-management');
    }
}
