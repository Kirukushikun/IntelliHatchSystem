<?php

namespace App\Livewire\Shared\Management\IncubatorManagement;

use Livewire\Component;
use App\Models\Incubator;
use App\Traits\SanitizesInput;
use Illuminate\Support\Facades\Cache;

class Edit extends Component
{
    use SanitizesInput;
    
    public $incubatorId = '';
    public $incubatorName = '';
    public $showModal = false;
    public $originalIncubatorName;

    protected $rules = [
        'incubatorName' => 'required|string|max:255',
    ];

    protected $messages = [
        'incubatorName.required' => 'Incubator name is required.',
        'incubatorName.max' => 'Incubator name must not exceed 255 characters.',
    ];

    protected $listeners = ['openEditModal' => 'openModal'];

    public function openModal($incubatorId)
    {
        $this->incubatorId = $incubatorId;
        $cacheKey = 'management:incubators:' . (int) $this->incubatorId;
        $incubator = Cache::remember($cacheKey, 300, fn () => Incubator::find($this->incubatorId));
        
        if ($incubator) {
            $this->incubatorName = $incubator->incubatorName;
            $this->originalIncubatorName = $incubator->incubatorName;
            $this->resetValidation();
            $this->showModal = true;
        }
    }

    public function updatedIncubatorName()
    {
        // If the name is the same as original, remove unique validation
        if ($this->incubatorName === $this->originalIncubatorName) {
            $this->rules['incubatorName'] = 'required|string|max:255';
        } else {
            $this->rules['incubatorName'] = 'required|string|max:255|unique:incubator-machines,incubatorName';
        }
    }

    public function updateIncubator()
    {
        // Sanitize input before validation
        $this->incubatorName = $this->sanitizeName($this->incubatorName);
        
        $this->updatedIncubatorName(); // Update validation rules
        $this->validate();

        try {
            $incubator = Incubator::findOrFail($this->incubatorId);
            
            $incubator->update([
                'incubatorName' => $this->incubatorName,
            ]);

            Cache::forget('management:incubators:all');
            Cache::forget('management:incubators:' . (int) $this->incubatorId);

            $incubatorName = $this->incubatorName; // Store name before closing modal
            $this->closeModal();
            $this->dispatch('refreshIncubators');
            $this->dispatch('showToast', message: "{$incubatorName} has been successfully updated!", type: 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('showToast', message: 'Failed to update incubator. Please try again.', type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['incubatorName', 'incubatorId', 'originalIncubatorName']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.shared.management.incubator-management.edit-incubator-management');
    }
}
