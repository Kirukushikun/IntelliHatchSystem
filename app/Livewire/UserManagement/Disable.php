<?php

namespace App\Livewire\UserManagement;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Disable extends Component
{
    public $showModal = false;
    public $userId = '';
    public $userName = '';
    public $isCurrentlyDisabled = false;
    public $processing = false;

    protected $listeners = ['openDisableModal' => 'openModal'];

    public function openModal($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return;
        }

        $this->userId = $userId;
        $this->userName = $user->first_name . ' ' . $user->last_name;
        $this->isCurrentlyDisabled = $user->is_disabled;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['userId', 'userName', 'isCurrentlyDisabled', 'processing']);
    }

    public function toggleDisable()
    {
        $this->processing = true;

        try {
            $user = User::find($this->userId);
            if (!$user) {
                $this->dispatch('showToast', message: 'User not found', type: 'error');
                return;
            }

            // Prevent disabling the current authenticated user
            if ($user->id === Auth::user()->id) {
                $this->dispatch('showToast', message: 'You cannot disable your own account', type: 'error');
                return;
            }

            $newStatus = !$this->isCurrentlyDisabled;
            $user->update([
                'is_disabled' => $newStatus
            ]);

            $action = $newStatus ? 'disabled' : 'enabled';
            $userName = $this->userName; // Store before closing modal
            $this->closeModal();
            $this->dispatch('showToast', message: "{$userName}'s account has been {$action}", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Error updating account status', type: 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.user-management.disable-user-management');
    }
}
