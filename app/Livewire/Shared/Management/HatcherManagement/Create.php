<?php

namespace App\Livewire\Shared\Management\HatcherManagement;

use Livewire\Component;
use App\Models\Hatcher;
use App\Traits\SanitizesInput;

class Create extends Component
{
    use SanitizesInput;
    
    public $hatcherName = '';
    public $showModal = false;

    protected $rules = [
        'hatcherName' => 'required|string|max:255|unique:hatcher-machines,hatcherName',
    ];

    protected $messages = [
        'hatcherName.required' => 'Hatcher name is required.',
        'hatcherName.unique' => 'A hatcher with this name already exists.',
        'hatcherName.max' => 'Hatcher name must not exceed 255 characters.',
    ];

    protected $listeners = ['openCreateModal' => 'openModal'];

    public function openModal()
    {
        $this->reset(['hatcherName']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function createHatcher()
    {
        // Sanitize input before validation
        $this->hatcherName = $this->sanitizeInput($this->hatcherName);
        
        $this->validate();

        try {
            Hatcher::create([
                'hatcherName' => $this->hatcherName,
                'isActive' => false, // Default to inactive
            ]);

            $hatcherName = $this->hatcherName; // Store name before closing modal
            $this->closeModal();
            $this->dispatch('refreshHatchers');
            $this->dispatch('showToast', message: "{$hatcherName} has been successfully created!", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors will be displayed automatically
            // Just re-throw to let Livewire handle validation display
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to create hatcher. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['hatcherName']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.shared.management.hatcher-management.create-hatcher-management');
    }
}
