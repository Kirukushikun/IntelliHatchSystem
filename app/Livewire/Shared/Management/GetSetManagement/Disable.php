<?php

namespace App\Livewire\Shared\Management\GetSetManagement;

use Livewire\Component;
use App\Models\GetSet;
use Illuminate\Support\Facades\Cache;

class Disable extends Component
{
    public $getSetId = '';
    public $getSetName = '';
    public $isActive = false;
    public $showModal = false;
    public $processing = false;

    protected $listeners = ['openDisableModal' => 'openModal'];

    public function openModal($getSetId)
    {
        $this->getSetId = $getSetId;
        $cacheKey = 'management:get-sets:' . (int) $this->getSetId;
        $getSet = Cache::remember($cacheKey, 300, fn () => GetSet::find($this->getSetId));

        if ($getSet) {
            $this->getSetName = $getSet->getSetName;
            $this->isActive = $getSet->isActive;
            $this->showModal = true;
        }
    }

    public function toggleStatus()
    {
        $this->processing = true;

        try {
            $getSet = GetSet::findOrFail($this->getSetId);

            $getSet->update([
                'isActive' => !$this->isActive,
            ]);

            Cache::forget('management:get-sets:all');
            Cache::forget('management:get-sets:' . (int) $this->getSetId);

            $action = $this->isActive ? 'deactivated' : 'activated';
            $getSetNameValue = $this->getSetName;
            $this->closeModal();
            $this->dispatch('refreshGetSets');
            $this->dispatch('showToast', message: "{$getSetNameValue} has been successfully {$action}!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update GetSet status. Please try again.', type: 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->processing = false;
        $this->reset(['getSetId', 'getSetName', 'isActive']);
    }

    public function render()
    {
        return view('livewire.shared.management.get-set-management.disable-get-set-management');
    }
}
