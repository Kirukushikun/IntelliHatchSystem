<?php

namespace App\Livewire\UserManagement;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetPassword extends Component
{
    public $showModal = false;
    public $userId = '';
    public $userName = '';
    public $processing = false;

    protected $listeners = ['openResetPasswordModal' => 'openModal'];

    public function openModal($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return;
        }

        $this->userId = $userId;
        $this->userName = $user->first_name . ' ' . $user->last_name;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['userId', 'userName', 'processing']);
    }

    public function resetPassword()
    {
        $this->processing = true;

        try {
            $user = User::find($this->userId);
            if (!$user) {
                $this->dispatch('showToast', message: 'User not found', type: 'error');
                return;
            }

            // Reset to default password (brookside25)
            $user->update([
                'password' => Hash::make('brookside25')
            ]);

            $userName = $this->userName; // Store before closing modal
            $this->closeModal();
            $this->dispatch('showToast', message: "{$userName}'s password has been reset", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Error resetting password', type: 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.user-management.reset-password-user-management');
    }
}
