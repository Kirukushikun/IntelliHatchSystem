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

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Form Types</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Manage production impact tags for each form type.</p>
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
                <li class="flex items-center justify-between gap-4 px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                    {{-- Name + current badge --}}
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="text-sm text-gray-900 dark:text-white truncate">{{ $ft->form_name }}</span>
                        @if($badgeClass)
                            <span class="shrink-0 text-xs font-medium px-1.5 py-0.5 rounded {{ $badgeClass }}">{{ $badgeLabel }}</span>
                        @endif
                    </div>

                    {{-- Impact level dropdown --}}
                    <select
                        wire:change="updateImpactLevel({{ $ft->id }}, $event.target.value)"
                        class="shrink-0 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent dark:scheme-dark"
                    >
                        <option value="" @selected(!$ft->impact_level)>— None —</option>
                        <option value="direct" @selected($ft->impact_level === 'direct')>Direct</option>
                        <option value="direct_indirect" @selected($ft->impact_level === 'direct_indirect')>Direct + Indirect</option>
                        <option value="indirect" @selected($ft->impact_level === 'indirect')>Indirect</option>
                        <option value="support" @selected($ft->impact_level === 'support')>Support</option>
                    </select>
                </li>
            @endforeach
        </ul>
    </div>
</div>
