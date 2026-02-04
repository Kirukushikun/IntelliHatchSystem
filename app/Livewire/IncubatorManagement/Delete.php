<?php

namespace App\Livewire\IncubatorManagement;

use Livewire\Component;
use App\Models\Incubator;

class Delete extends Component
{
    public $incubatorId = '';
    public $incubatorName = '';
    public $showModal = false;

    protected $listeners = ['openDeleteModal' => 'openModal'];

    public function openModal($incubatorId)
    {
        $this->incubatorId = $incubatorId;
        $incubator = Incubator::find($this->incubatorId);
        
        if ($incubator) {
            $this->incubatorName = $incubator->incubatorName;
            $this->showModal = true;
        }
    }

    public function deleteIncubator()
    {
        try {
            $incubator = Incubator::findOrFail($this->incubatorId);
            $incubatorName = $incubator->incubatorName;
            
            $incubator->delete();

            $this->closeModal();
            $this->dispatch('refreshIncubators');
            $this->dispatch('showToast', message: "{$incubatorName} has been successfully deleted!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to delete incubator. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['incubatorId', 'incubatorName']);
    }

    public function render()
    {
        return view('livewire.incubator-management.delete-incubator-management');
    }
}
