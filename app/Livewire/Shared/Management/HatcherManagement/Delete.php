<?php

namespace App\Livewire\Shared\Management\HatcherManagement;

use Livewire\Component;
use App\Models\Hatcher;

class Delete extends Component
{
    public $hatcherId = '';
    public $hatcherName = '';
    public $showModal = false;

    protected $listeners = ['openDeleteModal' => 'openModal'];

    public function openModal($hatcherId)
    {
        $this->hatcherId = $hatcherId;
        $hatcher = Hatcher::find($this->hatcherId);
        
        if ($hatcher) {
            $this->hatcherName = $hatcher->hatcherName;
            $this->showModal = true;
        }
    }

    public function deleteHatcher()
    {
        try {
            $hatcher = Hatcher::findOrFail($this->hatcherId);
            $hatcherName = $hatcher->hatcherName;
            
            $hatcher->delete();

            $this->closeModal();
            $this->dispatch('refreshHatchers');
            $this->dispatch('showToast', message: "{$hatcherName} has been successfully deleted!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to delete hatcher. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['hatcherId', 'hatcherName']);
    }

    public function render()
    {
        return view('livewire.shared.management.hatcher-management.delete-hatcher-management');
    }
}
