<?php

namespace App\Livewire\HatcherManagement;

use Livewire\Component;
use App\Models\Hatcher;

class Disable extends Component
{
    public $hatcherId = '';
    public $hatcherName = '';
    public $isDisabled = false;
    public $showModal = false;
    public $processing = false;

    protected $listeners = ['openDisableModal' => 'openModal'];

    public function openModal($hatcherId)
    {
        $this->hatcherId = $hatcherId;
        $hatcher = Hatcher::find($this->hatcherId);
        
        if ($hatcher) {
            $this->hatcherName = $hatcher->hatcherName;
            $this->isDisabled = $hatcher->isDisabled;
            $this->showModal = true;
        }
    }

    public function toggleStatus()
    {
        $this->processing = true;
        
        try {
            $hatcher = Hatcher::findOrFail($this->hatcherId);
            
            $hatcher->update([
                'isDisabled' => !$this->isDisabled,
            ]);

            $action = $this->isDisabled ? 'enabled' : 'disabled';
            $hatcherName = $this->hatcherName; // Store name before closing modal
            $this->closeModal();
            $this->dispatch('refreshHatchers');
            $this->dispatch('showToast', message: "{$hatcherName} has been successfully {$action}!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update hatcher status. Please try again.', type: 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->processing = false;
        $this->reset(['hatcherId', 'hatcherName', 'isDisabled']);
    }

    public function render()
    {
        return view('livewire.hatcher-management.disable-hatcher-management');
    }
}
