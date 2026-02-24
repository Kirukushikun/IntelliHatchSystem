<?php

namespace App\Livewire\Shared\Management\HatcherManagement;

use Livewire\Component;
use App\Models\Hatcher;
use Illuminate\Support\Facades\Cache;

class Delete extends Component
{
    public $hatcherId = '';
    public $hatcherName = '';
    public $showModal = false;

    protected $listeners = ['openDeleteModal' => 'openModal'];

    public function openModal($hatcherId)
    {
        $this->hatcherId = $hatcherId;
        $cacheKey = 'management:hatchers:' . (int) $this->hatcherId;
        $hatcher = Cache::remember($cacheKey, 300, fn () => Hatcher::find($this->hatcherId));
        
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

            Cache::forget('management:hatchers:all');
            Cache::forget('management:hatchers:' . (int) $this->hatcherId);

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
