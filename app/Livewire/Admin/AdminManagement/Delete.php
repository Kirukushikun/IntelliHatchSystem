<?php

namespace App\Livewire\Admin\AdminManagement;

use Livewire\Component;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Delete extends Component
{
    public $userId = '';
    public $userName = '';
    public $showModal = false;

    protected $listeners = ['openDeleteAdminModal' => 'openModal'];

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
            $this->dispatch('showToast', message: 'You cannot delete the primary superadmin account.', type: 'error');
            return;
        }

        $this->userId = $userId;
        $this->userName = $user->first_name . ' ' . $user->last_name;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['userId', 'userName']);
    }

    public function deleteAdmin()
    {
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
                $this->dispatch('showToast', message: 'You cannot delete the primary superadmin account.', type: 'error');
                $this->closeModal();
                return;
            }

            $userName = $user->first_name . ' ' . $user->last_name;
            ActivityLogger::log('deleted_admin', "Deleted admin {$userName}", 'User', (int) $this->userId);
            $user->delete();

            Cache::forget('management:admins:' . (int) $this->userId);
            Cache::forget('admin_management:first_superadmin_id');

            $this->closeModal();
            $this->dispatch('refreshAdmins');
            $this->dispatch('showToast', message: "{$userName} has been deleted successfully!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to delete account. Please try again.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.admin-management.delete');
    }
}
