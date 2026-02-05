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
    public $processing = false;

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
        $this->processing = true;
        
        try {
            $incubator = Incubator::findOrFail($this->incubatorId);
            
            $incubator->update([
                'isDisabled' => !$this->isDisabled,
            ]);

            $action = $this->isDisabled ? 'enabled' : 'disabled';
            $incubatorName = $this->incubatorName; // Store name before closing modal
            $this->closeModal();
            $this->dispatch('refreshIncubators');
            $this->dispatch('showToast', message: "{$incubatorName} has been successfully {$action}!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update incubator status. Please try again.', type: 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->processing = false;
        $this->reset(['incubatorId', 'incubatorName', 'isDisabled']);
    }

    public function render()
    {
        return view('livewire.incubator-management.disable-incubator-management');
    }
}
