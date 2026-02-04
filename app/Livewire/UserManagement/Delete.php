<?php

namespace App\Livewire\UserManagement;

use Livewire\Component;
use App\Models\HatcheryUser;

class Delete extends Component
{
    public $userId = '';
    public $userName = '';
    public $showModal = false;

    protected $listeners = ['openDeleteModal' => 'openModal'];

    public function openModal($userId)
    {
        $this->userId = $userId;
        $user = HatcheryUser::find($userId);
        
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
            $user = HatcheryUser::find($this->userId);
            
            if ($user) {
                $userName = $user->first_name . ' ' . $user->last_name;
                $user->delete();

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
        return view('livewire.user-management.delete-user-management');
    }
}