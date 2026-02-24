<?php

namespace App\Livewire\Shared\Management\PlenumManagement;

use Livewire\Component;
use App\Models\Plenum;
use Illuminate\Support\Facades\Cache;

class Delete extends Component
{
    public $plenumId = '';
    public $plenumName = '';
    public $showModal = false;

    protected $listeners = ['openDeleteModal' => 'openModal'];

    public function openModal($plenumId)
    {
        $this->plenumId = $plenumId;
        $cacheKey = 'management:plenums:' . (int) $this->plenumId;
        $plenum = Cache::remember($cacheKey, 300, fn () => Plenum::find($this->plenumId));
        
        if ($plenum) {
            $this->plenumName = $plenum->plenumName;
            $this->showModal = true;
        }
    }

    public function deletePlenum()
    {
        try {
            $plenum = Plenum::findOrFail($this->plenumId);
            $plenumName = $plenum->plenumName;
            
            $plenum->delete();

            Cache::forget('management:plenums:all');
            Cache::forget('management:plenums:' . (int) $this->plenumId);

            $this->closeModal();
            $this->dispatch('refreshPlenums');
            $this->dispatch('showToast', message: "{$plenumName} has been successfully deleted!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to delete plenum. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['plenumId', 'plenumName']);
    }

    public function render()
    {
        return view('livewire.shared.management.plenum-management.delete-plenum-management');
    }
}
