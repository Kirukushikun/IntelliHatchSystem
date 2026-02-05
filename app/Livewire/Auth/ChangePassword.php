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
                'confirmed',
                'different:currentPassword'
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
        'newPassword.confirmed' => 'Password confirmation does not match',
        'newPassword.different' => 'New password must be different from current password',
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

            // Reset form fields
            $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
            
            // Dispatch success event for toast notification
            $this->dispatch('password-changed', message: "Your password has been changed successfully!");
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let Livewire handle validation errors automatically
            $this->validate();
        } catch (\Exception $e) {
            $this->addError('newPassword', 'An error occurred while changing your password. Please try again.');
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.auth.change-password');
    }
}
