<?php

namespace App\Livewire\Admin\SystemPrompts;

use App\Models\SystemPrompt;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Display extends Component
{
    public string $search = '';
    public string $statusFilter = 'all'; // all, active, inactive, archived

    // Create modal
    public bool $showCreateModal = false;
    public string $createName = '';
    public string $createPrompt = '';

    // Edit modal
    public bool $showEditModal = false;
    public ?int $editingId = null;
    public string $editName = '';
    public string $editPrompt = '';

    // Archive confirmation modal
    public bool $showArchiveModal = false;
    public ?int $archivingId = null;
    public string $archivingName = '';

    // Delete confirmation modal
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;
    public string $deletingName = '';

    protected function rules(): array
    {
        return [
            'createName' => 'required|string|max:255',
            'createPrompt' => 'required|string',
            'editName' => 'required|string|max:255',
            'editPrompt' => 'required|string',
        ];
    }

    public function updatingSearch(): void
    {
        // reset to first page equivalent — nothing needed since no pagination
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->createName = '';
        $this->createPrompt = '';
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function saveCreate(): void
    {
        $this->validateOnly('createName');
        $this->validateOnly('createPrompt');

        SystemPrompt::create([
            'name' => trim($this->createName),
            'prompt' => trim($this->createPrompt),
            'is_active' => false,
            'is_archived' => false,
            'created_by' => Auth::id(),
        ]);

        $this->showCreateModal = false;
        $this->createName = '';
        $this->createPrompt = '';
        session()->flash('success', 'System prompt created successfully.');
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function openEdit(int $id): void
    {
        $prompt = SystemPrompt::findOrFail($id);
        $this->editingId = $id;
        $this->editName = $prompt->name;
        $this->editPrompt = $prompt->prompt;
        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function saveEdit(): void
    {
        $this->validateOnly('editName');
        $this->validateOnly('editPrompt');

        $prompt = SystemPrompt::findOrFail($this->editingId);
        $prompt->update([
            'name' => trim($this->editName),
            'prompt' => trim($this->editPrompt),
        ]);

        $this->showEditModal = false;
        $this->editingId = null;
        session()->flash('success', 'System prompt updated successfully.');
    }

    // ── Active toggle ─────────────────────────────────────────────────────────

    public function toggleActive(int $id): void
    {
        $prompt = SystemPrompt::findOrFail($id);

        if ($prompt->is_archived) {
            session()->flash('error', 'Archived prompts cannot be activated.');
            return;
        }

        if ($prompt->is_active) {
            // Deactivate
            $prompt->update(['is_active' => false]);
            session()->flash('success', "'{$prompt->name}' deactivated.");
        } else {
            // Deactivate all others first, then activate this one
            SystemPrompt::where('is_active', true)->update(['is_active' => false]);
            $prompt->update(['is_active' => true]);
            session()->flash('success', "'{$prompt->name}' is now the active prompt.");
        }
    }

    // ── Duplicate ─────────────────────────────────────────────────────────────

    public function duplicate(int $id): void
    {
        $prompt = SystemPrompt::findOrFail($id);

        SystemPrompt::create([
            'name' => $prompt->name . ' (Copy)',
            'prompt' => $prompt->prompt,
            'is_active' => false,
            'is_archived' => false,
            'created_by' => Auth::id(),
        ]);

        session()->flash('success', "'{$prompt->name}' duplicated successfully.");
    }

    // ── Archive ───────────────────────────────────────────────────────────────

    public function openArchive(int $id): void
    {
        $prompt = SystemPrompt::findOrFail($id);
        $this->archivingId = $id;
        $this->archivingName = $prompt->name;
        $this->showArchiveModal = true;
    }

    public function confirmArchive(): void
    {
        $prompt = SystemPrompt::findOrFail($this->archivingId);
        $prompt->update([
            'is_archived' => true,
            'is_active' => false,
        ]);

        $this->showArchiveModal = false;
        $this->archivingId = null;
        $this->archivingName = '';
        session()->flash('success', 'System prompt archived.');
    }

    public function unarchive(int $id): void
    {
        $prompt = SystemPrompt::findOrFail($id);
        $prompt->update(['is_archived' => false]);
        session()->flash('success', "'{$prompt->name}' restored from archive.");
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function openDelete(int $id): void
    {
        $prompt = SystemPrompt::findOrFail($id);
        $this->deletingId = $id;
        $this->deletingName = $prompt->name;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        SystemPrompt::findOrFail($this->deletingId)->delete();

        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingName = '';
        session()->flash('success', 'System prompt permanently deleted.');
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $query = SystemPrompt::with('creator')->latest();

        if ($this->search !== '') {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        match ($this->statusFilter) {
            'active' => $query->where('is_active', true)->where('is_archived', false),
            'inactive' => $query->where('is_active', false)->where('is_archived', false),
            'archived' => $query->where('is_archived', true),
            default => null,
        };

        return view('livewire.admin.system-prompts.display', [
            'prompts' => $query->get(),
        ]);
    }
}
