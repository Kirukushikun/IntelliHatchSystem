<?php

namespace App\Livewire\Shared\Management\PsNumberManagement;

use Livewire\Component;
use App\Models\PsNumber;
use App\Traits\SanitizesInput;
use Illuminate\Support\Facades\Cache;

class Create extends Component
{
    use SanitizesInput;

    public $psNumber = '';
    public $showModal = false;

    protected $rules = [
        'psNumber' => 'required|string|max:255|unique:ps-numbers,psNumber',
    ];

    protected $messages = [
        'psNumber.required' => 'PS Number is required.',
        'psNumber.unique' => 'A PS Number with this value already exists.',
        'psNumber.max' => 'PS Number must not exceed 255 characters.',
    ];

    protected $listeners = ['openCreateModal' => 'openModal'];

    public function openModal()
    {
        $this->reset(['psNumber']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function createPsNumber()
    {
        $this->psNumber = $this->sanitizeName($this->psNumber);

        $this->validate();

        try {
            PsNumber::create([
                'psNumber' => $this->psNumber,
                'isActive' => false,
            ]);

            Cache::forget('management:ps-numbers:all');

            $psNumber = $this->psNumber;
            $this->closeModal();
            $this->dispatch('refreshPsNumbers');
            $this->dispatch('showToast', message: "{$psNumber} has been successfully added", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to add PS Number. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['psNumber']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.shared.management.ps-number-management.create-ps-number-management');
    }
}
