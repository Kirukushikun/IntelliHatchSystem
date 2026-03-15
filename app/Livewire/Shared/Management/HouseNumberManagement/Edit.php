<?php

namespace App\Livewire\Shared\Management\HouseNumberManagement;

use Livewire\Component;
use App\Models\HouseNumber;
use App\Traits\SanitizesInput;
use Illuminate\Support\Facades\Cache;

class Edit extends Component
{
    use SanitizesInput;

    public $houseNumberId = '';
    public $houseNumber = '';
    public $showModal = false;
    public $originalHouseNumber;

    protected $rules = [
        'houseNumber' => 'required|string|max:255',
    ];

    protected $messages = [
        'houseNumber.required' => 'House Number is required.',
        'houseNumber.max' => 'House Number must not exceed 255 characters.',
    ];

    protected $listeners = ['openEditModal' => 'openModal'];

    public function openModal($houseNumberId)
    {
        $this->houseNumberId = $houseNumberId;
        $cacheKey = 'management:house-numbers:' . (int) $this->houseNumberId;
        $houseNumber = Cache::remember($cacheKey, 300, fn () => HouseNumber::find($this->houseNumberId));

        if ($houseNumber) {
            $this->houseNumber = $houseNumber->houseNumber;
            $this->originalHouseNumber = $houseNumber->houseNumber;
            $this->resetValidation();
            $this->showModal = true;
        }
    }

    public function updatedHouseNumber()
    {
        if ($this->houseNumber === $this->originalHouseNumber) {
            $this->rules['houseNumber'] = 'required|string|max:255';
        } else {
            $this->rules['houseNumber'] = 'required|string|max:255|unique:house-numbers,houseNumber';
        }
    }

    public function updateHouseNumber()
    {
        $this->houseNumber = $this->sanitizeName($this->houseNumber);

        $this->updatedHouseNumber();
        $this->validate();

        try {
            $houseNumber = HouseNumber::findOrFail($this->houseNumberId);

            $houseNumber->update([
                'houseNumber' => $this->houseNumber,
            ]);

            Cache::forget('management:house-numbers:all');
            Cache::forget('management:house-numbers:' . (int) $this->houseNumberId);

            $houseNumberValue = $this->houseNumber;
            $this->closeModal();
            $this->dispatch('refreshHouseNumbers');
            $this->dispatch('showToast', message: "{$houseNumberValue} has been successfully updated!", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update House Number. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['houseNumber', 'houseNumberId', 'originalHouseNumber']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.shared.management.house-number-management.edit-house-number-management');
    }
}
