<?php

namespace App\Livewire\Admin\AdminManagement;

use Livewire\Component;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Disable extends Component
{
    public $showModal = false;
    public $userId = '';
    public $userName = '';
    public $isCurrentlyDisabled = false;
    public $processing = false;

    protected $listeners = ['openDisableAdminModal' => 'openModal'];

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
            $this->dispatch('showToast', message: 'You cannot disable the primary superadmin account.', type: 'error');
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
                $this->dispatch('showToast', message: 'Account not found.', type: 'error');
                return;
            }

            // Re-check guard on submit
            $firstSuperadminId = Cache::get('admin_management:first_superadmin_id',
                fn () => User::where('user_type', 0)->orderBy('id')->value('id')
            );

            if ($user->id === $firstSuperadminId && Auth::id() !== $firstSuperadminId) {
                $this->dispatch('showToast', message: 'You cannot disable the primary superadmin account.', type: 'error');
                $this->closeModal();
                return;
            }

            $user->update(['is_disabled' => !$this->isCurrentlyDisabled]);

            Cache::forget('management:admins:' . (int) $this->userId);

            $action = $this->isCurrentlyDisabled ? 'enabled' : 'disabled';
            $userName = $this->userName;
            ActivityLogger::log("{$action}_admin", ucfirst($action) . " admin {$userName}", 'User', (int) $this->userId);
            $this->closeModal();
            $this->dispatch('refreshAdmins');
            $this->dispatch('showToast', message: "{$userName} has been successfully {$action}!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update account status. Please try again.', type: 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.admin-management.disable');
    }
}
