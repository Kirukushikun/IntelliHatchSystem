<?php

namespace App\Livewire\Shared\Management\HatcherManagement;

use Livewire\Component;
use App\Models\Hatcher;
use Illuminate\Support\Facades\Cache;

class Disable extends Component
{
    public $hatcherId = '';
    public $hatcherName = '';
    public $isActive = false;
    public $showModal = false;
    public $processing = false;

    protected $listeners = ['openDisableModal' => 'openModal'];

    public function openModal($hatcherId)
    {
        $this->hatcherId = $hatcherId;
        $cacheKey = 'management:hatchers:' . (int) $this->hatcherId;
        $hatcher = Cache::remember($cacheKey, 300, fn () => Hatcher::find($this->hatcherId));
        
        if ($hatcher) {
            $this->hatcherName = $hatcher->hatcherName;
            $this->isActive = $hatcher->isActive;
            $this->showModal = true;
        }
    }

    public function toggleStatus()
    {
        $this->processing = true;
        
        try {
            $hatcher = Hatcher::findOrFail($this->hatcherId);
            
            $hatcher->update([
                'isActive' => !$this->isActive,
            ]);

            Cache::forget('management:hatchers:all');
            Cache::forget('management:hatchers:' . (int) $this->hatcherId);

            $action = !$this->isActive ? 'activated' : 'deactivated';
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
        $this->reset(['hatcherId', 'hatcherName', 'isActive']);
    }

    public function render()
    {
        return view('livewire.shared.management.hatcher-management.disable-hatcher-management');
    }
}
