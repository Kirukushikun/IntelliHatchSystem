<div>
    {{-- Flash messages --}}
    @if(session('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 text-sm"
        >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Form Types</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Manage form type names, descriptions, and production impact tags.</p>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap gap-2 mb-5">
        <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200">
            <span class="w-1.5 h-1.5 rounded-full bg-red-500 dark:bg-red-400"></span> Direct
        </span>
        <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-200">
            <span class="w-1.5 h-1.5 rounded-full bg-orange-500 dark:bg-orange-400"></span> Direct + Indirect
        </span>
        <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 dark:bg-blue-400"></span> Indirect
        </span>
        <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-200">
            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-gray-400"></span> Support
        </span>
    </div>

    {{-- Form Types List --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($formTypes as $ft)
                @php
                    [$badgeClass, $badgeLabel] = match($ft->impact_level) {
                        'direct'          => ['bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200', 'Direct'],
                        'direct_indirect' => ['bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-200', 'Direct + Indirect'],
                        'indirect'        => ['bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200', 'Indirect'],
                        'support'         => ['bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-200', 'Support'],
                        default           => [null, null],
                    };
                @endphp
                <li class="px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors {{ !$ft->isActive ? 'opacity-60' : '' }}">
                    @if($editingId === $ft->id)
                        {{-- Edit mode --}}
                        <form wire:submit="saveEdit" class="space-y-3">
                            <div class="flex flex-col sm:flex-row gap-3">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Form Name</label>
                                    <input
                                        type="text"
                                        wire:model="editName"
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent"
                                        placeholder="Form name"
                                    />
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Description</label>
                                    <input
                                        type="text"
                                        wire:model="editDescription"
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent"
                                        placeholder="Short description (optional)"
                                    />
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1.5 text-sm font-medium px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Save
                                </button>
                                <button
                                    type="button"
                                    wire:click="cancelEditing"
                                    class="inline-flex items-center gap-1.5 text-sm font-medium px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 transition-colors"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    @else
                        {{-- Display mode --}}
                        <div class="flex items-center justify-between gap-4">
                            {{-- Name + description + badge --}}
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="min-w-0">
                                    <span class="text-sm text-gray-900 dark:text-white truncate block">{{ $ft->form_name }}</span>
                                    @if($ft->description)
                                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate block">{{ $ft->description }}</span>
                                    @endif
                                </div>
                                @if($badgeClass)
                                    <span class="shrink-0 text-xs font-medium px-1.5 py-0.5 rounded {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                @endif
                                @if(!$ft->isActive)
                                    <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">Inactive</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                {{-- Toggle active/inactive --}}
                                <button
                                    wire:click="toggleStatus({{ $ft->id }})"
                                    wire:confirm="{{ $ft->isActive ? 'Deactivate this form type? It will be hidden from all users, dashboards, insights, and AI chat.' : 'Activate this form type? It will become available across the system.' }}"
                                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-colors cursor-pointer {{ $ft->isActive ? 'text-red-600 bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/20 dark:hover:bg-red-900/40' : 'text-green-600 bg-green-50 hover:bg-green-100 dark:text-green-400 dark:bg-green-900/20 dark:hover:bg-green-900/40' }}"
                                    title="{{ $ft->isActive ? 'Deactivate' : 'Activate' }}"
                                >
                                    {{ $ft->isActive ? 'Deactivate' : 'Activate' }}
                                </button>

                                {{-- Edit button --}}
                                <button
                                    wire:click="startEditing({{ $ft->id }})"
                                    class="text-gray-400 hover:text-orange-500 dark:text-gray-500 dark:hover:text-orange-400 transition-colors"
                                    title="Edit name & description"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                {{-- Impact level dropdown --}}
                                <select
                                    wire:change="updateImpactLevel({{ $ft->id }}, $event.target.value)"
                                    class="text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent dark:scheme-dark"
                                >
                                    <option value="" @selected(!$ft->impact_level)>— None —</option>
                                    <option value="direct" @selected($ft->impact_level === 'direct')>Direct</option>
                                    <option value="direct_indirect" @selected($ft->impact_level === 'direct_indirect')>Direct + Indirect</option>
                                    <option value="indirect" @selected($ft->impact_level === 'indirect')>Indirect</option>
                                    <option value="support" @selected($ft->impact_level === 'support')>Support</option>
                                </select>
                            </div>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</div>
