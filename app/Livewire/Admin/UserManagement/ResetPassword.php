<?php

namespace App\Livewire\Admin\UserManagement;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetPassword extends Component
{
    public $userId = '';
    public $userName = '';
    public $showModal = false;

    protected $listeners = ['openResetPasswordModal' => 'openModal'];

    public function openModal($userId)
    {
        $this->userId = $userId;
        $user = User::find($userId);
        
        if ($user) {
            $this->userName = $user->first_name . ' ' . $user->last_name;
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['userId', 'userName']);
    }

    public function resetPassword()
    {
        try {
            $user = User::find($this->userId);
            
            if ($user) {
                $userName = $user->first_name . ' ' . $user->last_name;
                
                // Reset password to default (using a common default password)
                $defaultPassword = 'password123';
                $user->password = Hash::make($defaultPassword);
                $user->save();

                $this->closeModal();
                $this->dispatch('passwordReset');
                $this->dispatch('showToast', message: "Password for {$userName} has been reset successfully!", type: 'success');
            }
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to reset password. Please try again.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.user-management.reset-password-user-management');
    }
}
