<?php

namespace App\Livewire\UserManagement;

use Livewire\Component;
use App\Models\HatcheryUser;

class Create extends Component
{
    public $firstName = '';
    public $lastName = '';
    public $showModal = false;

    protected $rules = [
        'firstName' => 'required|string|min:2|max:50',
        'lastName' => 'required|string|min:2|max:50',
    ];

    protected $listeners = ['openCreateModal' => 'openModal'];

    protected $messages = [
        'firstName.required' => 'First name is required',
        'firstName.min' => 'First name must be at least 2 characters',
        'firstName.max' => 'First name cannot exceed 50 characters',
        'lastName.required' => 'Last name is required',
        'lastName.min' => 'Last name must be at least 2 characters',
        'lastName.max' => 'Last name cannot exceed 50 characters',
    ];

    public function openModal()
    {
        $this->reset(['firstName', 'lastName']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['firstName', 'lastName']);
        $this->resetValidation();
    }

    public function createUser()
    {
        $this->validate();

        try {
            HatcheryUser::create([
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'is_disabled' => false,
                'created_date' => now(),
            ]);

            $this->closeModal();
            $this->dispatch('showToast', message: "User created successfully!", type: 'success');
            $this->reset(['firstName', 'lastName']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors will be displayed automatically
            // Just re-throw to let Livewire handle validation display
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to create user. Please try again.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.user-management.create-user-management');
    }
}