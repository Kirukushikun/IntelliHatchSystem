<?php

namespace App\Livewire\Shared\Management\PsNumberManagement;

use Livewire\Component;
use App\Models\PsNumber;
use Illuminate\Support\Facades\Cache;

class Disable extends Component
{
    public $psNumberId = '';
    public $psNumber = '';
    public $isActive = false;
    public $showModal = false;
    public $processing = false;

    protected $listeners = ['openDisableModal' => 'openModal'];

    public function openModal($psNumberId)
    {
        $this->psNumberId = $psNumberId;
        $cacheKey = 'management:ps-numbers:' . (int) $this->psNumberId;
        $psNumber = Cache::remember($cacheKey, 300, fn () => PsNumber::find($this->psNumberId));

        if ($psNumber) {
            $this->psNumber = $psNumber->psNumber;
            $this->isActive = $psNumber->isActive;
            $this->showModal = true;
        }
    }

    public function toggleStatus()
    {
        $this->processing = true;

        try {
            $psNumber = PsNumber::findOrFail($this->psNumberId);

            $psNumber->update([
                'isActive' => !$this->isActive,
            ]);

            Cache::forget('management:ps-numbers:all');
            Cache::forget('management:ps-numbers:' . (int) $this->psNumberId);

            $action = $this->isActive ? 'deactivated' : 'activated';
            $psNumberValue = $this->psNumber;
            $this->closeModal();
            $this->dispatch('refreshPsNumbers');
            $this->dispatch('showToast', message: "{$psNumberValue} has been successfully {$action}!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update PS Number status. Please try again.', type: 'error');
        } finally {
            $this->processing = false;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->processing = false;
        $this->reset(['psNumberId', 'psNumber', 'isActive']);
    }

    public function render()
    {
        return view('livewire.shared.management.ps-number-management.disable-ps-number-management');
    }
}
