<?php

namespace App\Livewire\Shared\Management\PlenumManagement;

use Livewire\Component;
use App\Models\Plenum;

class Edit extends Component
{
    public $plenumId = '';
    public $plenumName = '';
    public $showModal = false;
    public $originalPlenumName;

    protected $rules = [
        'plenumName' => 'required|string|max:255',
    ];

    protected $messages = [
        'plenumName.required' => 'Plenum name is required.',
        'plenumName.max' => 'Plenum name must not exceed 255 characters.',
    ];

    protected $listeners = ['openEditModal' => 'openModal'];

    public function openModal($plenumId)
    {
        $this->plenumId = $plenumId;
        $plenum = Plenum::find($this->plenumId);
        
        if ($plenum) {
            $this->plenumName = $plenum->plenumName;
            $this->originalPlenumName = $plenum->plenumName;
            $this->resetValidation();
            $this->showModal = true;
        }
    }

    public function updatedPlenumName()
    {
        // If the name is the same as original, remove unique validation
        if ($this->plenumName === $this->originalPlenumName) {
            $this->rules['plenumName'] = 'required|string|max:255';
        } else {
            $this->rules['plenumName'] = 'required|string|max:255|unique:plenum-machines,plenumName';
        }
    }

    public function updatePlenum()
    {
        $this->updatedPlenumName(); // Update validation rules
        $this->validate();

        try {
            $plenum = Plenum::findOrFail($this->plenumId);
            
            $plenum->update([
                'plenumName' => $this->plenumName,
            ]);

            $plenumName = $this->plenumName; // Store name before closing modal
            $this->closeModal();
            $this->dispatch('refreshPlenums');
            $this->dispatch('showToast', message: "{$plenumName} has been successfully updated!", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update plenum. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['plenumName', 'plenumId', 'originalPlenumName']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.shared.management.plenum-management.edit-plenum-management');
    }
}
