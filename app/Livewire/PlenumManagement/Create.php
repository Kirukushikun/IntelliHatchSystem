<?php

namespace App\Livewire\PlenumManagement;

use Livewire\Component;
use App\Models\Plenum;

class Create extends Component
{
    public $plenumName = '';
    public $showModal = false;

    protected $rules = [
        'plenumName' => 'required|string|max:255|unique:plenum-machines,plenumName',
    ];

    protected $messages = [
        'plenumName.required' => 'Plenum name is required.',
        'plenumName.max' => 'Plenum name must not exceed 255 characters.',
        'plenumName.unique' => 'Plenum name already exists.',
    ];

    protected $listeners = ['openCreateModal' => 'openModal'];

    public function openModal()
    {
        $this->reset(['plenumName']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function createPlenum()
    {
        $this->validate();

        try {
            Plenum::create([
                'plenumName' => $this->plenumName,
                'isActive' => false, // Default to inactive
                'creationDate' => now(),
            ]);

            $plenumName = $this->plenumName; // Store name before closing modal
            $this->closeModal();
            $this->dispatch('refreshPlenums');
            $this->dispatch('showToast', message: "{$plenumName} has been successfully created!", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to create plenum. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['plenumName']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.plenum-management.create-plenum-management');
    }
}
