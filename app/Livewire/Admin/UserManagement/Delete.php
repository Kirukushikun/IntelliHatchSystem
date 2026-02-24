<?php

namespace App\Livewire\Admin\UserManagement;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class Delete extends Component
{
    public $userId = '';
    public $userName = '';
    public $showModal = false;

    protected $listeners = ['openDeleteModal' => 'openModal'];

    public function openModal($userId)
    {
        $this->userId = $userId;
        $cacheKey = 'management:users:' . (int) $this->userId;
        $user = Cache::remember($cacheKey, 300, fn () => User::find($userId));
        
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

    public function deleteUser()
    {
        try {
            $user = User::find($this->userId);
            
            if ($user) {
                $userName = $user->first_name . ' ' . $user->last_name;
                $user->delete();

                Cache::forget('management:users:all');
                Cache::forget('management:users:' . (int) $this->userId);

                $this->closeModal();
                $this->dispatch('userDeleted');
                $this->dispatch('showToast', message: "{$userName} has been successfully deleted!", type: 'success');
            }
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to delete user. Please try again.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.user-management.delete-user-management');
    }
}