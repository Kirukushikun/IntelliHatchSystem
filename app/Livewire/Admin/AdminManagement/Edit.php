<?php

namespace App\Livewire\Admin\AdminManagement;

use Livewire\Component;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Traits\SanitizesInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Edit extends Component
{
    use SanitizesInput;

    public $userId = '';
    public $firstName = '';
    public $lastName = '';
    public $showModal = false;

    protected $rules = [
        'firstName' => 'required|string|min:2|max:50',
        'lastName' => 'required|string|min:2|max:50',
    ];

    protected $listeners = ['openEditAdminModal' => 'openModal'];

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
        $cacheKey = 'management:admins:' . (int) $this->userId;
        $user = Cache::remember($cacheKey, 300, fn () => User::find($this->userId));

        if (!$user) {
            return;
        }

        // Guard: cannot edit the first superadmin unless you ARE the first superadmin
        $firstSuperadminId = Cache::get('admin_management:first_superadmin_id',
            fn () => User::where('user_type', 0)->orderBy('id')->value('id')
        );

        if ($user->id === $firstSuperadminId && Auth::id() !== $firstSuperadminId) {
            $this->dispatch('showToast', message: 'You cannot edit the primary superadmin account.', type: 'error');
            return;
        }

        $this->firstName = $user->first_name;
        $this->lastName = $user->last_name;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['firstName', 'lastName', 'userId']);
        $this->resetValidation();
    }

    public function updateAdmin()
    {
        $this->firstName = $this->sanitizeName($this->firstName);
        $this->lastName = $this->sanitizeName($this->lastName);

        $this->validate();

        try {
            $user = User::find($this->userId);

            if (!$user) {
                $this->dispatch('showToast', message: 'Account not found.', type: 'error');
                return;
            }

            // Re-check guard on submit
            $firstSuperadminId = Cache::get('admin_management:first_superadmin_id',
                fn () => User::where('user_type', 0)->orderBy('id')->value('id')
            );

            if ($user->id === $firstSuperadminId && Auth::id() !== $firstSuperadminId) {
                $this->dispatch('showToast', message: 'You cannot edit the primary superadmin account.', type: 'error');
                $this->closeModal();
                return;
            }

            $baseUsername = strtoupper(substr($this->firstName, 0, 1)) . $this->lastName;
            $username = $baseUsername;
            $counter = 1;

            while (User::where('username', $username)->where('id', '!=', $this->userId)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $user->update([
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'username' => $username,
            ]);

            Cache::forget('management:admins:' . (int) $this->userId);

            $fullName = $this->firstName . ' ' . $this->lastName;
            ActivityLogger::log('updated_admin', "Updated admin {$fullName}", 'User', (int) $this->userId);
            $this->closeModal();
            $this->dispatch('showToast', message: "{$fullName} has been updated successfully!", type: 'success');
            $this->dispatch('refreshAdmins');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update account. Please try again.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.admin-management.edit');
    }
}
