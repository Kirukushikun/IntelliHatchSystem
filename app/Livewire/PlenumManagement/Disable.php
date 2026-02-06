<?php

namespace App\Livewire\PlenumManagement;

use Livewire\Component;
use App\Models\Plenum;

class Disable extends Component
{
    public $plenumId = '';
    public $plenumName = '';
    public $isDisabled = false;
    public $showModal = false;
    public $processing = false;

    protected $listeners = ['openDisableModal' => 'openModal'];

    public function openModal($plenumId)
    {
        $this->plenumId = $plenumId;
        $plenum = Plenum::find($this->plenumId);
        
        if ($plenum) {
            $this->plenumName = $plenum->plenumName;
            $this->isDisabled = $plenum->isDisabled;
            $this->showModal = true;
        }
    }

    public function toggleStatus()
    {
        $this->processing = true;
        
        try {
            $plenum = Plenum::findOrFail($this->plenumId);
            
            $plenum->update([
                'isDisabled' => !$this->isDisabled,
            ]);

            $action = $this->isDisabled ? 'enabled' : 'disabled';
            $plenumName = $this->plenumName; // Store name before closing modal
            $this->closeModal();
            $this->dispatch('refreshPlenums');
            $this->dispatch('showToast', message: "{$plenumName} has been successfully {$action}!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update plenum status. Please try again.', type: 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->processing = false;
        $this->reset(['plenumId', 'plenumName', 'isDisabled']);
    }

    public function render()
    {
        return view('livewire.plenum-management.disable-plenum-management');
    }
}
