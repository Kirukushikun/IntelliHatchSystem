<?php

namespace App\Livewire\Shared\Management\GetSetManagement;

use Livewire\Component;
use App\Models\GetSet;
use App\Traits\SanitizesInput;
use Illuminate\Support\Facades\Cache;

class Edit extends Component
{
    use SanitizesInput;

    public $getSetId = '';
    public $getSetName = '';
    public $showModal = false;
    public $originalGetSetName;

    protected $rules = [
        'getSetName' => 'required|string|max:255',
    ];

    protected $messages = [
        'getSetName.required' => 'GetSet Name is required.',
        'getSetName.max' => 'GetSet Name must not exceed 255 characters.',
    ];

    protected $listeners = ['openEditModal' => 'openModal'];

    public function openModal($getSetId)
    {
        $this->getSetId = $getSetId;
        $cacheKey = 'management:get-sets:' . (int) $this->getSetId;
        $getSet = Cache::remember($cacheKey, 300, fn () => GetSet::find($this->getSetId));

        if ($getSet) {
            $this->getSetName = $getSet->getSetName;
            $this->originalGetSetName = $getSet->getSetName;
            $this->resetValidation();
            $this->showModal = true;
        }
    }

    public function updatedGetSetName()
    {
        if ($this->getSetName === $this->originalGetSetName) {
            $this->rules['getSetName'] = 'required|string|max:255';
        } else {
            $this->rules['getSetName'] = 'required|string|max:255|unique:get-sets,getSetName';
        }
    }

    public function updateGetSet()
    {
        $this->getSetName = $this->sanitizeName($this->getSetName);

        $this->updatedGetSetName();
        $this->validate();

        try {
            $getSet = GetSet::findOrFail($this->getSetId);

            $getSet->update([
                'getSetName' => $this->getSetName,
            ]);

            Cache::forget('management:get-sets:all');
            Cache::forget('management:get-sets:' . (int) $this->getSetId);

            $getSetNameValue = $this->getSetName;
            $this->closeModal();
            $this->dispatch('refreshGetSets');
            $this->dispatch('showToast', message: "{$getSetNameValue} has been successfully updated!", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update GetSet. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['getSetName', 'getSetId', 'originalGetSetName']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.shared.management.get-set-management.edit-get-set-management');
    }
}
