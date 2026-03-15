<?php

namespace App\Livewire\Shared\Management\HouseNumberManagement;

use Livewire\Component;
use App\Models\HouseNumber;
use Illuminate\Support\Facades\Cache;

class Delete extends Component
{
    public $houseNumberId = '';
    public $houseNumber = '';
    public $showModal = false;

    protected $listeners = ['openDeleteModal' => 'openModal'];

    public function openModal($houseNumberId)
    {
        $this->houseNumberId = $houseNumberId;
        $cacheKey = 'management:house-numbers:' . (int) $this->houseNumberId;
        $houseNumber = Cache::remember($cacheKey, 300, fn () => HouseNumber::find($this->houseNumberId));

        if ($houseNumber) {
            $this->houseNumber = $houseNumber->houseNumber;
            $this->showModal = true;
        }
    }

    public function deleteHouseNumber()
    {
        try {
            $houseNumber = HouseNumber::findOrFail($this->houseNumberId);
            $houseNumberValue = $houseNumber->houseNumber;

            $houseNumber->delete();

            Cache::forget('management:house-numbers:all');
            Cache::forget('management:house-numbers:' . (int) $this->houseNumberId);

            $this->closeModal();
            $this->dispatch('refreshHouseNumbers');
            $this->dispatch('showToast', message: "{$houseNumberValue} has been successfully deleted!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to delete House Number. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['houseNumberId', 'houseNumber']);
    }

    public function render()
    {
        return view('livewire.shared.management.house-number-management.delete-house-number-management');
    }
}
