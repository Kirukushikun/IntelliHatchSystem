<?php

namespace App\Livewire\Shared\Management\HouseNumberManagement;

use Livewire\Component;
use App\Models\HouseNumber;
use App\Traits\SanitizesInput;
use Illuminate\Support\Facades\Cache;

class Create extends Component
{
    use SanitizesInput;

    public $houseNumber = '';
    public $showModal = false;

    protected $rules = [
        'houseNumber' => 'required|string|max:255|unique:house-numbers,houseNumber',
    ];

    protected $messages = [
        'houseNumber.required' => 'House Number is required.',
        'houseNumber.unique' => 'A House Number with this value already exists.',
        'houseNumber.max' => 'House Number must not exceed 255 characters.',
    ];

    protected $listeners = ['openCreateModal' => 'openModal'];

    public function openModal()
    {
        $this->reset(['houseNumber']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function createHouseNumber()
    {
        $this->houseNumber = $this->sanitizeName($this->houseNumber);

        $this->validate();

        try {
            HouseNumber::create([
                'houseNumber' => $this->houseNumber,
                'isActive' => false,
            ]);

            Cache::forget('management:house-numbers:all');

            $houseNumber = $this->houseNumber;
            $this->closeModal();
            $this->dispatch('refreshHouseNumbers');
            $this->dispatch('showToast', message: "{$houseNumber} has been successfully added", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to add House Number. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['houseNumber']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.shared.management.house-number-management.create-house-number-management');
    }
}
