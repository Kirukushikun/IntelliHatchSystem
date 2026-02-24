<?php

namespace App\Livewire\Admin\UserManagement;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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
        $cacheKey = 'management:users:' . (int) $userId;
        $user = Cache::remember($cacheKey, 300, fn () => User::find($userId));
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

            $user->update([
                'is_disabled' => !$this->isCurrentlyDisabled,
            ]);

            Cache::forget('management:users:all');
            Cache::forget('management:users:' . (int) $this->userId);

            $action = $this->isCurrentlyDisabled ? 'enabled' : 'disabled';
            $userName = $this->userName; // Store name before closing modal
            $this->closeModal();
            $this->dispatch('refreshUsers');
            $this->dispatch('showToast', message: "{$userName} has been successfully {$action}!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update user status. Please try again.', type: 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.user-management.disable-user-management');
    }
}
