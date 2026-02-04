<?php

namespace App\Livewire\IncubatorManagement;

use Livewire\Component;
use App\Models\Incubator;

class Create extends Component
{
    public $incubatorName = '';
    public $showModal = false;

    protected $rules = [
        'incubatorName' => 'required|string|max:255|unique:incubators,incubatorName',
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
        $this->validate();

        try {
            Incubator::create([
                'incubatorName' => $this->incubatorName,
                'isDisabled' => false, // Default to enabled
            ]);

            $this->closeModal();
            $this->dispatch('refreshIncubators');
            $this->dispatch('showToast', message: "{$this->incubatorName} has been successfully added!", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors will be displayed automatically
            // Just re-throw to let Livewire handle validation display
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to create incubator. Please try again.', type: 'error');
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
        return view('livewire.incubator-management.create-incubator-management');
    }
}
