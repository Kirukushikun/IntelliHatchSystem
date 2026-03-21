<?php

namespace App\Livewire\Admin\AdminManagement;

use Livewire\Component;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class ChangePassword extends Component
{
    public $userId = '';
    public $userName = '';
    public $password = '';
    public $passwordConfirmation = '';
    public $showModal = false;

    protected $rules = [
        'password' => 'required|string|min:8|max:100',
        'passwordConfirmation' => 'required|same:password',
    ];

    protected $listeners = ['openChangePasswordAdminModal' => 'openModal'];

    protected $messages = [
        'password.required' => 'Password is required',
        'password.min' => 'Password must be at least 8 characters',
        'passwordConfirmation.required' => 'Please confirm the password',
        'passwordConfirmation.same' => 'Passwords do not match',
    ];

    public function openModal($userId)
    {
        $cacheKey = 'management:admins:' . (int) $userId;
        $user = Cache::remember($cacheKey, 300, fn () => User::find($userId));

        if (!$user) {
            return;
        }

        $firstSuperadminId = Cache::get('admin_management:first_superadmin_id',
            fn () => User::where('user_type', 0)->orderBy('id')->value('id')
        );

        if ($user->id === $firstSuperadminId && Auth::id() !== $firstSuperadminId) {
            $this->dispatch('showToast', message: 'You cannot change the password of the primary superadmin.', type: 'error');
            return;
        }

        $this->userId = $userId;
        $this->userName = $user->first_name . ' ' . $user->last_name;
        $this->reset(['password', 'passwordConfirmation']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['userId', 'userName', 'password', 'passwordConfirmation']);
        $this->resetValidation();
    }

    public function changePassword()
    {
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
                $this->dispatch('showToast', message: 'You cannot change the password of the primary superadmin.', type: 'error');
                $this->closeModal();
                return;
            }

            $user->password = Hash::make($this->password);
            $user->save();

            $userName = $this->userName;
            ActivityLogger::log('changed_admin_password', "Changed password for admin {$userName}", 'User', (int) $this->userId);
            $this->closeModal();
            $this->dispatch('showToast', message: "Password for {$userName} has been changed successfully!", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to change password. Please try again.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.admin-management.change-password');
    }
}
