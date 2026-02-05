<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ChangePassword extends Component
{
    public $currentPassword = '';
    public $newPassword = '';
    public $newPassword_confirmation = '';
    public $showModal = false;
    public $processing = false;
    public $showSuccess = false;

    protected function rules()
    {
        return [
            'currentPassword' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, Auth::user()->password)) {
                        $fail('Current password is incorrect');
                    }
                }
            ],
            'newPassword' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:currentPassword'
            ],
        ];
    }

    protected $messages = [
        'currentPassword.required' => 'Current password is required',
        'currentPassword.string' => 'Current password must be a string',
        'newPassword.required' => 'New password is required',
        'newPassword.string' => 'New password must be a string',
        'newPassword.min' => 'Password must be at least 8 characters long',
        'newPassword.confirmed' => 'Password confirmation does not match',
        'newPassword.different' => 'New password must be different from current password',
    ];

    public function openModal()
    {
        $this->reset(['currentPassword', 'newPassword', 'newPassword_confirmation', 'showModal']);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['currentPassword', 'newPassword', 'newPassword_confirmation']);
        $this->resetErrorBag();
    }

    public function changePassword()
    {
        // Validate required fields first
        $this->validate([
            'currentPassword' => ['required'],
            'newPassword' => ['required'],
            'newPassword_confirmation' => ['required'],
        ]);

        $user = Auth::user();

        // Check current password manually before validation
        if (!Hash::check($this->currentPassword, $user->password)) {
            // Password is wrong - throw validation error
            throw ValidationException::withMessages([
                'currentPassword' => 'The current password is incorrect.',
            ]);
        }

        // Current password is correct - proceed with new password validation
        $this->validate([
            'newPassword' => [
                'required',
                'min:8',
                'different:currentPassword'
            ],
            'newPassword_confirmation' => ['required'],
        ]);

        // Manually check password confirmation
        if ($this->newPassword !== $this->newPassword_confirmation) {
            $this->addError('newPassword_confirmation', 'The password confirmation does not match.');
            return;
        }

        // Update password
        $user->password = Hash::make($this->newPassword);
        $user->save();

        // Reset form
        $this->reset(['currentPassword', 'newPassword', 'newPassword_confirmation']);
        
        // Show success message
        $this->showSuccess = true;
        
        // Dispatch success event for toast notification
        $this->dispatch('showToast', message: "Your password has been changed successfully!", type: 'success');
    }

    public function render()
    {
        return view('livewire.auth.change-password');
    }
}
