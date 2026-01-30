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
                // Generate new username based on updated name
                $newUsername = $this->generateUsername($this->firstName, $this->lastName);
                
                $user->update([
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'username' => $newUsername,
                ]);

                $this->closeModal();
                $this->dispatch('userUpdated');
                $this->dispatch('showToast', message: "{$this->firstName} {$this->lastName} has been successfully modified!", type: 'success');
            }
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update user. Please try again.', type: 'error');
        }
    }

    private function generateUsername($firstName, $lastName)
    {
        // Split last name into parts and take max 2
        $lastNameParts = explode(' ', $lastName);
        $lastNameParts = array_slice($lastNameParts, 0, 2); // Take only first 2 parts
        
        // Rejoin with no spaces and capitalize first letter
        $cleanLastName = str_replace(' ', '', implode(' ', $lastNameParts));
        $baseUsername = strtoupper(substr($cleanLastName, 0, 1)) . ucfirst($cleanLastName);
        
        // Check if base username exists (exclude current user)
        $existingUser = User::where('username', $baseUsername)
                            ->where('id', '!=', $this->userId)
                            ->first();
        
        if (!$existingUser) {
            return $baseUsername;
        } else {
            // Find the next available number
            $counter = 1;
            do {
                $newUsername = $baseUsername . $counter;
                $existingUser = User::where('username', $newUsername)
                                    ->where('id', '!=', $this->userId)
                                    ->first();
                $counter++;
            } while ($existingUser);
            
            return $newUsername;
        }
    }

    public function render()
    {
        return view('livewire.user-management.edit-user-management');
    }
}