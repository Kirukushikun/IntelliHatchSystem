<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class ChangePassword extends Component
{
    public $currentPassword = '';
    public $newPassword = '';
    public $newPasswordConfirmation = '';
    public $showModal = false;
    public $processing = false;

    protected function rules()
    {
        return [
            'currentPassword' => ['required'],
            'newPassword' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->rules('regex:/^(?!.*password|.*12345678|.*qwerty)/i')
            ],
        ];
    }

    protected $messages = [
        'currentPassword.required' => 'Current password is required',
        'newPassword.required' => 'New password is required',
        'newPassword.confirmed' => 'Password confirmation does not match',
        'newPassword.min' => 'Password must be at least 8 characters long',
    ];

    public function openModal()
    {
        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation', 'showModal']);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
        $this->resetErrorBag();
    }

    public function changePassword()
    {
        $this->processing = true;

        try {
            $this->validate();

            $user = Auth::user();

            // Verify current password
            if (!Hash::check($this->currentPassword, $user->password)) {
                $this->addError('currentPassword', 'Current password is incorrect');
                return;
            }

            // Update password
            $user->update([
                'password' => Hash::make($this->newPassword)
            ]);

            $this->closeModal();
            
            // Dispatch success event for toast notification
            $user = Auth::user();
            $this->dispatch('password-changed', message: "Your password has been changed successfully!");
        } catch (\Exception $e) {
            // Handle any exceptions
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.auth.change-password');
    }
}
