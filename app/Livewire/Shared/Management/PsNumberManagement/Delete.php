<?php

namespace App\Livewire\Shared\Management\PsNumberManagement;

use Livewire\Component;
use App\Models\PsNumber;
use Illuminate\Support\Facades\Cache;

class Delete extends Component
{
    public $psNumberId = '';
    public $psNumber = '';
    public $showModal = false;

    protected $listeners = ['openDeleteModal' => 'openModal'];

    public function openModal($psNumberId)
    {
        $this->psNumberId = $psNumberId;
        $cacheKey = 'management:ps-numbers:' . (int) $this->psNumberId;
        $psNumber = Cache::remember($cacheKey, 300, fn () => PsNumber::find($this->psNumberId));

        if ($psNumber) {
            $this->psNumber = $psNumber->psNumber;
            $this->showModal = true;
        }
    }

    public function deletePsNumber()
    {
        try {
            $psNumber = PsNumber::findOrFail($this->psNumberId);
            $psNumberValue = $psNumber->psNumber;

            $psNumber->delete();

            Cache::forget('management:ps-numbers:all');
            Cache::forget('management:ps-numbers:' . (int) $this->psNumberId);

            $this->closeModal();
            $this->dispatch('refreshPsNumbers');
            $this->dispatch('showToast', message: "{$psNumberValue} has been successfully deleted!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to delete PS Number. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['psNumberId', 'psNumber']);
    }

    public function render()
    {
        return view('livewire.shared.management.ps-number-management.delete-ps-number-management');
    }
}
