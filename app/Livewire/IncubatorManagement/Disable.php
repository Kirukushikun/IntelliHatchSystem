<?php

namespace App\Livewire\IncubatorManagement;

use Livewire\Component;
use App\Models\Incubator;

class Disable extends Component
{
    public $incubatorId = '';
    public $incubatorName = '';
    public $isDisabled = false;
    public $showModal = false;

    protected $listeners = ['openDisableModal' => 'openModal'];

    public function openModal($incubatorId)
    {
        $this->incubatorId = $incubatorId;
        $incubator = Incubator::find($this->incubatorId);
        
        if ($incubator) {
            $this->incubatorName = $incubator->incubatorName;
            $this->isDisabled = $incubator->isDisabled;
            $this->showModal = true;
        }
    }

    public function toggleStatus()
    {
        try {
            $incubator = Incubator::findOrFail($this->incubatorId);
            
            $incubator->update([
                'isDisabled' => !$this->isDisabled,
            ]);

            $action = $this->isDisabled ? 'disabled' : 'enabled';
            $this->closeModal();
            $this->dispatch('refreshIncubators');
            $this->dispatch('showToast', message: "{$this->incubatorName} has been successfully {$action}!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update incubator status. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['incubatorId', 'incubatorName', 'isDisabled']);
    }

    public function render()
    {
        return view('livewire.incubator-management.disable-incubator-management');
    }
}
