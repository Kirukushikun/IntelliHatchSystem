<?php

namespace App\Livewire\Shared\Management\PsNumberManagement;

use Livewire\Component;
use App\Models\PsNumber;
use App\Traits\SanitizesInput;
use Illuminate\Support\Facades\Cache;

class Edit extends Component
{
    use SanitizesInput;

    public $psNumberId = '';
    public $psNumber = '';
    public $showModal = false;
    public $originalPsNumber;

    protected $rules = [
        'psNumber' => 'required|string|max:255',
    ];

    protected $messages = [
        'psNumber.required' => 'PS Number is required.',
        'psNumber.max' => 'PS Number must not exceed 255 characters.',
    ];

    protected $listeners = ['openEditModal' => 'openModal'];

    public function openModal($psNumberId)
    {
        $this->psNumberId = $psNumberId;
        $cacheKey = 'management:ps-numbers:' . (int) $this->psNumberId;
        $psNumber = Cache::remember($cacheKey, 300, fn () => PsNumber::find($this->psNumberId));

        if ($psNumber) {
            $this->psNumber = $psNumber->psNumber;
            $this->originalPsNumber = $psNumber->psNumber;
            $this->resetValidation();
            $this->showModal = true;
        }
    }

    public function updatedPsNumber()
    {
        if ($this->psNumber === $this->originalPsNumber) {
            $this->rules['psNumber'] = 'required|string|max:255';
        } else {
            $this->rules['psNumber'] = 'required|string|max:255|unique:ps-numbers,psNumber';
        }
    }

    public function updatePsNumber()
    {
        $this->psNumber = $this->sanitizeName($this->psNumber);

        $this->updatedPsNumber();
        $this->validate();

        try {
            $psNumber = PsNumber::findOrFail($this->psNumberId);

            $psNumber->update([
                'psNumber' => $this->psNumber,
            ]);

            Cache::forget('management:ps-numbers:all');
            Cache::forget('management:ps-numbers:' . (int) $this->psNumberId);

            $psNumberValue = $this->psNumber;
            $this->closeModal();
            $this->dispatch('refreshPsNumbers');
            $this->dispatch('showToast', message: "{$psNumberValue} has been successfully updated!", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update PS Number. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['psNumber', 'psNumberId', 'originalPsNumber']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.shared.management.ps-number-management.edit-ps-number-management');
    }
}
