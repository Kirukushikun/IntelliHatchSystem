<?php

namespace App\Livewire\Shared\Management\HouseNumberManagement;

use Livewire\Component;
use App\Models\HouseNumber;
use Illuminate\Support\Facades\Cache;

class Disable extends Component
{
    public $houseNumberId = '';
    public $houseNumber = '';
    public $isActive = false;
    public $showModal = false;
    public $processing = false;

    protected $listeners = ['openDisableModal' => 'openModal'];

    public function openModal($houseNumberId)
    {
        $this->houseNumberId = $houseNumberId;
        $cacheKey = 'management:house-numbers:' . (int) $this->houseNumberId;
        $houseNumber = Cache::remember($cacheKey, 300, fn () => HouseNumber::find($this->houseNumberId));

        if ($houseNumber) {
            $this->houseNumber = $houseNumber->houseNumber;
            $this->isActive = $houseNumber->isActive;
            $this->showModal = true;
        }
    }

    public function toggleStatus()
    {
        $this->processing = true;

        try {
            $houseNumber = HouseNumber::findOrFail($this->houseNumberId);

            $houseNumber->update([
                'isActive' => !$this->isActive,
            ]);

            Cache::forget('management:house-numbers:all');
            Cache::forget('management:house-numbers:' . (int) $this->houseNumberId);

            $action = $this->isActive ? 'deactivated' : 'activated';
            $houseNumberValue = $this->houseNumber;
            $this->closeModal();
            $this->dispatch('refreshHouseNumbers');
            $this->dispatch('showToast', message: "{$houseNumberValue} has been successfully {$action}!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update House Number status. Please try again.', type: 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->processing = false;
        $this->reset(['houseNumberId', 'houseNumber', 'isActive']);
    }

    public function render()
    {
        return view('livewire.shared.management.house-number-management.disable-house-number-management');
    }
}
