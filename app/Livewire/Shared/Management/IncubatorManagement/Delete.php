<?php

namespace App\Livewire\Shared\Management\IncubatorManagement;

use Livewire\Component;
use App\Models\Incubator;
use Illuminate\Support\Facades\Cache;

class Delete extends Component
{
    public $incubatorId = '';
    public $incubatorName = '';
    public $showModal = false;

    protected $listeners = ['openDeleteModal' => 'openModal'];

    public function openModal($incubatorId)
    {
        $this->incubatorId = $incubatorId;
        $cacheKey = 'management:incubators:' . (int) $this->incubatorId;
        $incubator = Cache::remember($cacheKey, 300, fn () => Incubator::find($this->incubatorId));
        
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

            Cache::forget('management:incubators:all');
            Cache::forget('management:incubators:' . (int) $this->incubatorId);

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
        return view('livewire.shared.management.incubator-management.delete-incubator-management');
    }
}
