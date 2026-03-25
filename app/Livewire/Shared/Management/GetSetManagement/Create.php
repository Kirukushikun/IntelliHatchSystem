<?php

namespace App\Livewire\Shared\Management\GetSetManagement;

use Livewire\Component;
use App\Models\GetSet;
use App\Traits\SanitizesInput;
use Illuminate\Support\Facades\Cache;

class Create extends Component
{
    use SanitizesInput;

    public $getSetName = '';
    public $showModal = false;

    protected $rules = [
        'getSetName' => 'required|string|max:255|unique:get-sets,getSetName',
    ];

    protected $messages = [
        'getSetName.required' => 'GetSet Name is required.',
        'getSetName.unique' => 'A GetSet with this name already exists.',
        'getSetName.max' => 'GetSet Name must not exceed 255 characters.',
    ];

    protected $listeners = ['openCreateModal' => 'openModal'];

    public function openModal()
    {
        $this->reset(['getSetName']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function createGetSet()
    {
        $this->getSetName = $this->sanitizeName($this->getSetName);

        $this->validate();

        try {
            GetSet::create([
                'getSetName' => $this->getSetName,
                'isActive' => false,
            ]);

            Cache::forget('management:get-sets:all');

            $getSetName = $this->getSetName;
            $this->closeModal();
            $this->dispatch('refreshGetSets');
            $this->dispatch('showToast', message: "{$getSetName} has been successfully added", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to add GetSet. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['getSetName']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.shared.management.get-set-management.create-get-set-management');
    }
}
