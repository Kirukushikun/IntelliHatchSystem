<?php

namespace App\Livewire\Shared\Management\GetSetManagement;

use Livewire\Component;
use App\Models\GetSet;
use Illuminate\Support\Facades\Cache;

class Delete extends Component
{
    public $getSetId = '';
    public $getSetName = '';
    public $showModal = false;

    protected $listeners = ['openDeleteModal' => 'openModal'];

    public function openModal($getSetId)
    {
        $this->getSetId = $getSetId;
        $cacheKey = 'management:get-sets:' . (int) $this->getSetId;
        $getSet = Cache::remember($cacheKey, 300, fn () => GetSet::find($this->getSetId));

        if ($getSet) {
            $this->getSetName = $getSet->getSetName;
            $this->showModal = true;
        }
    }

    public function deleteGetSet()
    {
        try {
            $getSet = GetSet::findOrFail($this->getSetId);
            $getSetNameValue = $getSet->getSetName;

            $getSet->delete();

            Cache::forget('management:get-sets:all');
            Cache::forget('management:get-sets:' . (int) $this->getSetId);

            $this->closeModal();
            $this->dispatch('refreshGetSets');
            $this->dispatch('showToast', message: "{$getSetNameValue} has been successfully deleted!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to delete GetSet. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['getSetId', 'getSetName']);
    }

    public function render()
    {
        return view('livewire.shared.management.get-set-management.delete-get-set-management');
    }
}
