<?php

namespace App\Livewire\UserManagement;

use Livewire\Component;
use App\Models\User;

class Edit extends Component
{
    public $userId = '';
    public $firstName = '';
    public $lastName = '';
    public $showModal = false;

    protected $rules = [
        'firstName' => 'required|string|min:2|max:50',
        'lastName' => 'required|string|min:2|max:50',
    ];

    protected $listeners = ['openEditModal' => 'openModal'];

    protected $messages = [
        'firstName.required' => 'First name is required',
        'firstName.min' => 'First name must be at least 2 characters',
        'firstName.max' => 'First name cannot exceed 50 characters',
        'lastName.required' => 'Last name is required',
        'lastName.min' => 'Last name must be at least 2 characters',
        'lastName.max' => 'Last name cannot exceed 50 characters',
    ];

    public function openModal($userId)
    {
        $this->userId = $userId;
        $user = User::find($this->userId);
        
        if ($user) {
            $this->firstName = $user->first_name;
            $this->lastName = $user->last_name;
            $this->resetValidation();
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['firstName', 'lastName', 'userId']);
        $this->resetValidation();
    }

    public function updateUser()
    {
        $this->validate();

        try {
            $user = User::find($this->userId);
            
            if ($user) {
                // Generate username based on updated name
                $baseUsername = strtoupper(substr($this->firstName, 0, 1)) . $this->lastName;
                $username = $baseUsername;
                $counter = 1;
                
                // Check if username exists and increment if needed (exclude current user)
                while (User::where('username', $username)->where('id', '!=', $this->userId)->exists()) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }
                
                $user->update([
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'username' => $username,
                ]);

                $fullName = $this->firstName . ' ' . $this->lastName; // Store full name before closing modal
                $this->closeModal();
                $this->dispatch('showToast', message: "{$fullName} has been updated successfully!", type: 'success');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update user. Please try again.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.user-management.edit-user-management');
    }
}