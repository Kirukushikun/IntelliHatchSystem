<?php

namespace App\Livewire\Shared\Management\PlenumManagement;

use Livewire\Component;
use App\Models\Plenum;
use App\Traits\SanitizesInput;
use Illuminate\Support\Facades\Cache;

class Create extends Component
{
    use SanitizesInput;
    
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
        // Sanitize input before validation
        $this->plenumName = $this->sanitizeName($this->plenumName);
        
        $this->validate();

        try {
            Plenum::create([
                'plenumName' => $this->plenumName,
                'isActive' => false, // Default to inactive
                'creationDate' => now(),
            ]);

            Cache::forget('management:plenums:all');

            $plenumName = $this->plenumName; // Store name before closing modal
            $this->closeModal();
            $this->dispatch('refreshPlenums');
            $this->dispatch('showToast', message: "{$plenumName} has been successfully added!", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to add plenum. Please try again.', type: 'error');
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
        return view('livewire.shared.management.plenum-management.create-plenum-management');
    }
}
