<?php

namespace App\Livewire\Admin\AdminManagement;

use Livewire\Component;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Traits\SanitizesInput;
use Illuminate\Support\Facades\Cache;

class Create extends Component
{
    use SanitizesInput;

    public $firstName = '';
    public $lastName = '';
    public $role = 1; // 0 = superadmin, 1 = admin
    public $showModal = false;

    protected $rules = [
        'firstName' => 'required|string|min:2|max:50',
        'lastName' => 'required|string|min:2|max:50',
        'role' => 'required|in:0,1',
    ];

    protected $listeners = ['openCreateAdminModal' => 'openModal'];

    protected $messages = [
        'firstName.required' => 'First name is required',
        'firstName.min' => 'First name must be at least 2 characters',
        'firstName.max' => 'First name cannot exceed 50 characters',
        'lastName.required' => 'Last name is required',
        'lastName.min' => 'Last name must be at least 2 characters',
        'lastName.max' => 'Last name cannot exceed 50 characters',
        'role.required' => 'Role is required',
        'role.in' => 'Invalid role selected',
    ];

    public function openModal()
    {
        $this->reset(['firstName', 'lastName', 'role']);
        $this->role = 1;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['firstName', 'lastName', 'role']);
        $this->resetValidation();
    }

    public function createAdmin()
    {
        $this->firstName = $this->sanitizeName($this->firstName);
        $this->lastName = $this->sanitizeName($this->lastName);

        $this->validate();

        try {
            $baseUsername = strtoupper(substr($this->firstName, 0, 1)) . $this->lastName;
            $username = $baseUsername;
            $counter = 1;

            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            User::create([
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'user_type' => (int) $this->role,
                'is_disabled' => false,
                'username' => $username,
                'password' => bcrypt('brookside25'),
            ]);

            Cache::forget('admin_management:first_superadmin_id');

            $fullName = $this->firstName . ' ' . $this->lastName;
            ActivityLogger::log('created_admin', "Created admin {$fullName}", 'User', User::where('username', $username)->value('id'));
            $this->closeModal();
            $this->dispatch('showToast', message: "{$fullName} has been created successfully!", type: 'success');
            $this->dispatch('refreshAdmins');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to create account. Please try again.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.admin-management.create');
    }
}
