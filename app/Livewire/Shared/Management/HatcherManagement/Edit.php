<?php

namespace App\Livewire\Shared\Management\HatcherManagement;

use Livewire\Component;
use App\Models\Hatcher;

class Edit extends Component
{
    public $hatcherId = '';
    public $hatcherName = '';
    public $showModal = false;
    public $originalHatcherName;

    protected $rules = [
        'hatcherName' => 'required|string|max:255',
    ];

    protected $messages = [
        'hatcherName.required' => 'Hatcher name is required.',
        'hatcherName.max' => 'Hatcher name must not exceed 255 characters.',
    ];

    protected $listeners = ['openEditModal' => 'openModal'];

    public function openModal($hatcherId)
    {
        $this->hatcherId = $hatcherId;
        $hatcher = Hatcher::find($this->hatcherId);
        
        if ($hatcher) {
            $this->hatcherName = $hatcher->hatcherName;
            $this->originalHatcherName = $hatcher->hatcherName;
            $this->resetValidation();
            $this->showModal = true;
        }
    }

    public function updatedHatcherName()
    {
        // If the name is the same as original, remove unique validation
        if ($this->hatcherName === $this->originalHatcherName) {
            $this->rules['hatcherName'] = 'required|string|max:255';
        } else {
            $this->rules['hatcherName'] = 'required|string|max:255|unique:hatcher-machines,hatcherName';
        }
    }

    public function updateHatcher()
    {
        $this->updatedHatcherName(); // Update validation rules
        $this->validate();

        try {
            $hatcher = Hatcher::findOrFail($this->hatcherId);
            
            $hatcher->update([
                'hatcherName' => $this->hatcherName,
            ]);

            $hatcherName = $this->hatcherName; // Store name before closing modal
            $this->closeModal();
            $this->dispatch('refreshHatchers');
            $this->dispatch('showToast', message: "{$hatcherName} has been successfully updated!", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update hatcher. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['hatcherName', 'hatcherId', 'originalHatcherName']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.shared.management.hatcher-management.edit-hatcher-management');
    }
}
