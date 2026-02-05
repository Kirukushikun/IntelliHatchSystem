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
                'max:128',
                'confirmed',
                'different:currentPassword',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'regex:/^(?!.*password|.*12345678|.*qwerty|.*asdfgh|.*zxcvbn)/i',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?]/'
            ],
            'newPasswordConfirmation' => [
                'required',
                'string'
            ],
        ];
    }

    protected $messages = [
        'currentPassword.required' => 'Current password is required',
        'currentPassword.string' => 'Current password must be a string',
        'newPassword.required' => 'New password is required',
        'newPassword.string' => 'New password must be a string',
        'newPassword.min' => 'Password must be at least 8 characters long',
        'newPassword.max' => 'Password cannot exceed 128 characters',
        'newPassword.confirmed' => 'Password confirmation does not match',
        'newPassword.different' => 'New password must be different from current password',
        'newPassword.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character',
        'newPassword.mixed' => 'Password must contain both uppercase and lowercase letters',
        'newPassword.numbers' => 'Password must contain at least one number',
        'newPassword.symbols' => 'Password must contain at least one special character',
        'newPassword.uncompromised' => 'This password has been exposed in data breaches. Please choose a different password.',
        'newPasswordConfirmation.required' => 'Password confirmation is required',
        'newPasswordConfirmation.string' => 'Password confirmation must be a string',
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
