<?php

namespace App\Livewire\Admin\FormTypes;

use App\Models\FormType;
use App\Models\Tag;
use App\Services\ActivityLogger;
use Livewire\Component;

class Display extends Component
{
    public ?int $editingId = null;

    public string $editName = '';

    public string $editDescription = '';

    public function startEditing(int $id): void
    {
        $formType = FormType::findOrFail($id);
        $this->editingId = $id;
        $this->editName = $formType->form_name;
        $this->editDescription = $formType->description ?? '';
    }

    public function cancelEditing(): void
    {
        $this->editingId = null;
        $this->editName = '';
        $this->editDescription = '';
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editName' => 'required|string|max:255|unique:form_types,form_name,' . $this->editingId,
            'editDescription' => 'nullable|string|max:500',
        ]);

        $formType = FormType::findOrFail($this->editingId);
        $oldName = $formType->form_name;
        $oldDescription = $formType->description;

        $formType->update([
            'form_name' => $this->editName,
            'description' => $this->editDescription ?: null,
        ]);

        ActivityLogger::log(
            action: 'update',
            description: "Form type \"{$oldName}\" updated",
            module: 'FormType',
            subjectId: $formType->id,
            properties: [
                'old_name' => $oldName,
                'new_name' => $this->editName,
                'old_description' => $oldDescription,
                'new_description' => $this->editDescription ?: null,
            ]
        );

        $this->cancelEditing();
        session()->flash('success', "Form type updated successfully.");
    }

    public function updateImpactLevel(int $id, ?string $level): void
    {
        $formType = FormType::findOrFail($id);

        $normalizedLevel = in_array($level, ['direct', 'direct_indirect', 'indirect', 'support'])
            ? $level
            : null;

        $formType->update(['impact_level' => $normalizedLevel]);

        ActivityLogger::log(
            action: 'update',
            description: "Impact level for \"{$formType->form_name}\" set to " . ($normalizedLevel ?? 'none'),
            module: 'FormType',
            subjectId: $formType->id,
            properties: ['impact_level' => $normalizedLevel]
        );

        session()->flash('success', "Tag updated for \"{$formType->form_name}\".");
    }

    public function updateUsageFrequency(int $id, ?string $frequency): void
    {
        $formType = FormType::findOrFail($id);

        $normalizedFrequency = in_array($frequency, ['daily', 'weekly', 'monthly'])
            ? $frequency
            : null;

        $formType->update(['usage_frequency' => $normalizedFrequency]);

        ActivityLogger::log(
            action: 'update',
            description: "Usage frequency for \"{$formType->form_name}\" set to " . ($normalizedFrequency ?? 'none'),
            module: 'FormType',
            subjectId: $formType->id,
            properties: ['usage_frequency' => $normalizedFrequency]
        );

        session()->flash('success', "Usage frequency updated for \"{$formType->form_name}\".");
    }

    public function toggleTag(int $formTypeId, int $tagId): void
    {
        $formType = FormType::findOrFail($formTypeId);
        $tag = Tag::findOrFail($tagId);

        if ($formType->tags()->where('tags.id', $tagId)->exists()) {
            $formType->tags()->detach($tagId);
            $action = 'removed';
        } else {
            $formType->tags()->attach($tagId);
            $action = 'added';
        }

        ActivityLogger::log(
            action: 'update',
            description: "Tag \"{$tag->name}\" {$action} for \"{$formType->form_name}\"",
            module: 'FormType',
            subjectId: $formType->id,
            properties: [
                'tag_id' => $tagId,
                'tag_name' => $tag->name,
                'action' => $action,
            ]
        );

        session()->flash('success', "Tag \"{$tag->name}\" {$action} for \"{$formType->form_name}\".");
    }

    public function toggleStatus(int $id): void
    {
        $formType = FormType::findOrFail($id);
        $newStatus = ! $formType->isActive;

        $formType->update(['isActive' => $newStatus]);

        $action = $newStatus ? 'enable' : 'disable';
        $label = $newStatus ? 'enabled' : 'disabled';

        ActivityLogger::log(
            action: $action,
            description: "Form type \"{$formType->form_name}\" {$label}",
            module: 'FormType',
            subjectId: $formType->id,
            properties: ['isActive' => $newStatus]
        );

        session()->flash('success', "Form type \"{$formType->form_name}\" has been {$label}.");
    }

    public function render()
    {
        return view('livewire.admin.form-types.display', [
            'formTypes' => FormType::with('tags')->orderBy('form_name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }
}
