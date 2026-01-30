<?php

namespace App\Livewire\UserManagement;

use Livewire\Component;
use App\Models\User;

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
            // Generate username automatically
            $username = $this->generateUsername();

            User::create([
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'username' => $username,
                'password' => bcrypt('brookside25'),
                'user_type' => 1, // Regular user
            ]);

            $this->closeModal();
            $this->dispatch('userCreated');
            $this->dispatch('showToast', message: "{$this->firstName} {$this->lastName} has been successfully added!", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors will be displayed automatically
            // Just re-throw to let Livewire handle validation display
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to create user. Please try again.', type: 'error');
        }
    }

    private function generateUsername()
    {
        // Split last name into parts and take max 2
        $lastNameParts = explode(' ', $this->lastName);
        $lastNameParts = array_slice($lastNameParts, 0, 2); // Take only first 2 parts
        
        // Rejoin with no spaces and capitalize first letter
        $cleanLastName = str_replace(' ', '', implode(' ', $lastNameParts));
        $baseUsername = strtoupper(substr($cleanLastName, 0, 1)) . ucfirst($cleanLastName);
        
        // Check if base username exists
        $existingUser = User::where('username', $baseUsername)->first();
        
        if (!$existingUser) {
            return $baseUsername;
        } else {
            // Find the next available number
            $counter = 1;
            do {
                $newUsername = $baseUsername . $counter;
                $existingUser = User::where('username', $newUsername)->first();
                $counter++;
            } while ($existingUser);
            
            return $newUsername;
        }
    }

    public function render()
    {
        return view('livewire.user-management.create-user-management');
    }
}