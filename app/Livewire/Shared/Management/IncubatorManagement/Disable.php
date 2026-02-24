<?php

namespace App\Livewire\Shared\Management\IncubatorManagement;

use Livewire\Component;
use App\Models\Incubator;
use Illuminate\Support\Facades\Cache;

class Disable extends Component
{
    public $incubatorId = '';
    public $incubatorName = '';
    public $isActive = false;
    public $showModal = false;
    public $processing = false;

    protected $listeners = ['openDisableModal' => 'openModal'];

    public function openModal($incubatorId)
    {
        $this->incubatorId = $incubatorId;
        $cacheKey = 'management:incubators:' . (int) $this->incubatorId;
        $incubator = Cache::remember($cacheKey, 300, fn () => Incubator::find($this->incubatorId));
        
        if ($incubator) {
            $this->incubatorName = $incubator->incubatorName;
            $this->isActive = $incubator->isActive;
            $this->showModal = true;
        }
    }

    public function toggleStatus()
    {
        $this->processing = true;
        
        try {
            $incubator = Incubator::findOrFail($this->incubatorId);
            
            $incubator->update([
                'isActive' => !$this->isActive,
            ]);

            Cache::forget('management:incubators:all');
            Cache::forget('management:incubators:' . (int) $this->incubatorId);

            $action = $this->isActive ? 'deactivated' : 'activated';
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
        $this->reset(['incubatorId', 'incubatorName', 'isActive']);
    }

    public function render()
    {
        return view('livewire.shared.management.incubator-management.disable-incubator-management');
    }
}
