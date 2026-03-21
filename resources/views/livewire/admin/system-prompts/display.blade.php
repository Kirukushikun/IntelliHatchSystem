<div class="space-y-6">

    {{-- Flash messages --}}
    @if (session()->has('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-300 rounded-lg text-sm">
            <svg class="w-4 h-4 shrink-0 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-300 rounded-lg text-sm">
            <svg class="w-4 h-4 shrink-0 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">System Prompts</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage AI prompt instructions for the Insight Generator. Only one prompt can be active at a time.</p>
        </div>
        <button wire:click="openCreate"
                class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Prompt
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"></path>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search prompts..."
                   class="w-full pl-9 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
        </div>
        <select wire:model.live="statusFilter"
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
            <option value="all" class="bg-white dark:bg-gray-700 text-gray-900 dark:text-white">All Prompts</option>
            <option value="active" class="bg-white dark:bg-gray-700 text-gray-900 dark:text-white">Active</option>
            <option value="inactive" class="bg-white dark:bg-gray-700 text-gray-900 dark:text-white">Inactive</option>
            <option value="archived" class="bg-white dark:bg-gray-700 text-gray-900 dark:text-white">Archived</option>
        </select>
    </div>

    {{-- Prompts list --}}
    @if ($prompts->isEmpty())
        <div class="text-center py-16 text-gray-400 dark:text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-sm font-medium">No prompts found</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($prompts as $prompt)
                <div class="bg-white dark:bg-gray-800 border rounded-xl shadow-sm overflow-hidden
                    {{ $prompt->is_archived
                        ? 'border-gray-200 dark:border-gray-700 opacity-75'
                        : ($prompt->is_active
                            ? 'border-orange-300 dark:border-orange-500 ring-1 ring-orange-200 dark:ring-orange-900'
                            : 'border-gray-200 dark:border-gray-700') }}">

                    {{-- Card header --}}
                    <div class="flex items-start justify-between px-5 py-4 gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="shrink-0">
                                @if ($prompt->is_archived)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-200 rounded-full">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8m-9 4v4m4-4v4"></path>
                                        </svg>
                                        Archived
                                    </span>
                                @elseif ($prompt->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-orange-100 dark:bg-orange-800/60 text-orange-700 dark:text-orange-200 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-orange-500 dark:bg-orange-300 rounded-full"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-200 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-gray-400 dark:bg-gray-300 rounded-full"></span>
                                        Inactive
                                    </span>
                                @endif
                            </div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white truncate">{{ $prompt->name }}</h3>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 shrink-0">
                            @if (!$prompt->is_archived)
                                {{-- Activate / Deactivate toggle --}}
                                <button wire:click="toggleActive({{ $prompt->id }})"
                                        title="{{ $prompt->is_active ? 'Deactivate' : 'Set as Active' }}"
                                        class="p-1.5 rounded-lg transition-colors cursor-pointer
                                               {{ $prompt->is_active
                                                   ? 'text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20'
                                                   : 'text-gray-400 dark:text-gray-400 hover:text-orange-500 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20' }}">
                                    <svg class="w-4 h-4" fill="{{ $prompt->is_active ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </button>

                                {{-- Duplicate --}}
                                <button wire:click="duplicate({{ $prompt->id }})"
                                        title="Duplicate"
                                        class="p-1.5 rounded-lg text-gray-400 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>

                                {{-- Edit --}}
                                <button wire:click="openEdit({{ $prompt->id }})"
                                        title="Edit"
                                        class="p-1.5 rounded-lg text-gray-400 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                {{-- Archive --}}
                                <button wire:click="openArchive({{ $prompt->id }})"
                                        title="Archive"
                                        class="p-1.5 rounded-lg text-gray-400 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8m-9 4v4m4-4v4"></path>
                                    </svg>
                                </button>
                            @else
                                {{-- Unarchive --}}
                                <button wire:click="unarchive({{ $prompt->id }})"
                                        title="Restore from Archive"
                                        class="p-1.5 rounded-lg text-gray-400 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                </button>
                            @endif

                            {{-- Delete --}}
                            <button wire:click="openDelete({{ $prompt->id }})"
                                    title="Delete"
                                    class="p-1.5 rounded-lg text-gray-400 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Prompt preview --}}
                    <div class="px-5 pb-4">
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 text-xs text-gray-600 dark:text-gray-400 font-mono leading-relaxed max-h-32 overflow-y-auto whitespace-pre-wrap">{{ Str::limit($prompt->prompt, 400) }}</div>
                    </div>

                    {{-- Card footer --}}
                    <div class="px-5 py-2.5 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs text-gray-400 dark:text-gray-500">
                        <span>Created by {{ $prompt->creator?->first_name }} {{ $prompt->creator?->last_name }}</span>
                        <span>{{ $prompt->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── CREATE MODAL ─────────────────────────────────────────────────────── --}}
    @if ($showCreateModal)
        <div x-data="{ fs: false }"
             class="fixed inset-0 z-50 flex bg-gray-900/60 dark:bg-gray-950/70"
             :class="fs ? 'items-stretch p-0' : 'items-center justify-center p-4'"
             wire:click.self="$set('showCreateModal', false)">
            <div class="bg-white dark:bg-gray-800 shadow-2xl w-full flex flex-col transition-all duration-200"
                 :class="fs ? 'h-full rounded-none' : 'rounded-2xl max-w-2xl max-h-[90vh]'">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">New System Prompt</h2>
                    <div class="flex items-center gap-1">
                        <button @click="fs = !fs" :title="fs ? 'Exit Fullscreen' : 'Fullscreen'" class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            <svg x-show="!fs" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"></path>
                            </svg>
                            <svg x-show="fs" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"></path>
                            </svg>
                        </button>
                        <button wire:click="$set('showCreateModal', false)" class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prompt Name <span class="text-red-500">*</span></label>
                        <input wire:model="createName" type="text" placeholder="e.g. Hatchery Analysis v2"
                               @class(['w-full px-3 py-2 border rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none',
                                       'border-red-400 dark:border-red-500' => $errors->has('createName'),
                                       'border-gray-300 dark:border-gray-600' => !$errors->has('createName')])>
                        @error('createName') <p class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prompt Content <span class="text-red-500">*</span></label>
                        <textarea wire:model="createPrompt" rows="14" placeholder="Enter the system prompt instructions for the AI..."
                                  @class(['w-full px-3 py-2 border rounded-lg text-sm font-mono bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none resize-y',
                                          'border-red-400 dark:border-red-500' => $errors->has('createPrompt'),
                                          'border-gray-300 dark:border-gray-600' => !$errors->has('createPrompt')])></textarea>
                        @error('createPrompt') <p class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <button wire:click="$set('showCreateModal', false)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button wire:click="saveCreate"
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors cursor-pointer">
                        Create Prompt
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── EDIT MODAL ───────────────────────────────────────────────────────── --}}
    @if ($showEditModal)
        <div x-data="{ fs: false }"
             class="fixed inset-0 z-50 flex bg-gray-900/60 dark:bg-gray-950/70"
             :class="fs ? 'items-stretch p-0' : 'items-center justify-center p-4'"
             wire:click.self="$set('showEditModal', false)">
            <div class="bg-white dark:bg-gray-800 shadow-2xl w-full flex flex-col transition-all duration-200"
                 :class="fs ? 'h-full rounded-none' : 'rounded-2xl max-w-2xl max-h-[90vh]'">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Edit System Prompt</h2>
                    <div class="flex items-center gap-1">
                        <button @click="fs = !fs" :title="fs ? 'Exit Fullscreen' : 'Fullscreen'" class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            <svg x-show="!fs" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"></path>
                            </svg>
                            <svg x-show="fs" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"></path>
                            </svg>
                        </button>
                        <button wire:click="$set('showEditModal', false)" class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prompt Name <span class="text-red-500">*</span></label>
                        <input wire:model="editName" type="text"
                               @class(['w-full px-3 py-2 border rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none',
                                       'border-red-400 dark:border-red-500' => $errors->has('editName'),
                                       'border-gray-300 dark:border-gray-600' => !$errors->has('editName')])>
                        @error('editName') <p class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prompt Content <span class="text-red-500">*</span></label>
                        <textarea wire:model="editPrompt" rows="14"
                                  @class(['w-full px-3 py-2 border rounded-lg text-sm font-mono bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none resize-y',
                                          'border-red-400 dark:border-red-500' => $errors->has('editPrompt'),
                                          'border-gray-300 dark:border-gray-600' => !$errors->has('editPrompt')])></textarea>
                        @error('editPrompt') <p class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <button wire:click="$set('showEditModal', false)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button wire:click="saveEdit"
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors cursor-pointer">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── ARCHIVE MODAL ────────────────────────────────────────────────────── --}}
    @if ($showArchiveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 dark:bg-gray-950/70" wire:click.self="$set('showArchiveModal', false)">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-6 text-center">
                    <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8m-9 4v4m4-4v4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Archive Prompt</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Archive <strong class="text-gray-700 dark:text-gray-200">"{{ $archivingName }}"</strong>? It will be deactivated and hidden from regular views. You can restore it later.</p>
                    <div class="flex gap-3 justify-center">
                        <button wire:click="$set('showArchiveModal', false)"
                                class="px-5 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button wire:click="confirmArchive"
                                class="px-5 py-2 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors cursor-pointer">
                            Archive
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── DELETE MODAL ─────────────────────────────────────────────────────── --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 dark:bg-gray-950/70" wire:click.self="$set('showDeleteModal', false)">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-6 text-center">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Prompt</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Permanently delete <strong class="text-gray-700 dark:text-gray-200">"{{ $deletingName }}"</strong>? This action cannot be undone.</p>
                    <div class="flex gap-3 justify-center">
                        <button wire:click="$set('showDeleteModal', false)"
                                class="px-5 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button wire:click="confirmDelete"
                                class="px-5 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors cursor-pointer">
                            Delete Permanently
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
