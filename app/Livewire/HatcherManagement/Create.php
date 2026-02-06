<?php

namespace App\Livewire\HatcherManagement;

use Livewire\Component;
use App\Models\Hatcher;

class Create extends Component
{
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
        $this->validate();

        try {
            Hatcher::create([
                'hatcherName' => $this->hatcherName,
                'isDisabled' => false, // Default to enabled
            ]);

            $this->closeModal();
            $this->dispatch('refreshHatchers');
            $this->dispatch('showToast', message: "{$this->hatcherName} has been successfully added!", type: 'success');
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
        return view('livewire.hatcher-management.create-hatcher-management');
    }
}
