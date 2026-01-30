@props([
    'currentPage' => 1,
    'lastPage' => 1,
    'pages' => [],
    'onPageChange' => null
])

<div class="flex space-x-1 justify-center sm:justify-end overflow-x-auto pb-2 sm:pb-0">
    {{-- Previous Page Link --}}
    @if ($currentPage == 1)
        <button type="button" class="px-2 md:px-3 py-1 min-w-9 min-h-9 text-xs md:text-sm font-normal text-slate-500 bg-white border border-slate-200 rounded cursor-not-allowed opacity-50" disabled>
            Prev
        </button>
    @else
        <button type="button" wire:click="{{ $onPageChange ?? 'gotoPage' }}({{ $currentPage - 1 }})" class="px-2 md:px-3 py-1 min-w-9 min-h-9 text-xs md:text-sm font-normal text-slate-500 bg-white border border-slate-200 rounded hover:bg-slate-50 hover:border-slate-400 transition duration-200 ease">
            Prev
        </button>
    @endif

    {{-- Page Numbers (max 3) --}}
    @foreach ($pages as $page)
        @if ($page == $currentPage)
            <button type="button" class="px-2 md:px-3 py-1 min-w-9 min-h-9 text-xs md:text-sm font-normal text-white bg-slate-800 border border-slate-800 rounded hover:bg-slate-600 hover:border-slate-600 transition duration-200 ease">
                {{ $page }}
            </button>
        @else
            <button type="button" wire:click="{{ $onPageChange ?? 'gotoPage' }}({{ $page }})" class="px-2 md:px-3 py-1 min-w-9 min-h-9 text-xs md:text-sm font-normal text-slate-500 bg-white border border-slate-200 rounded hover:bg-slate-50 hover:border-slate-400 transition duration-200 ease">
                {{ $page }}
            </button>
        @endif
    @endforeach

    {{-- Next Page Link --}}
    @if ($currentPage < $lastPage)
        <button type="button" wire:click="{{ $onPageChange ?? 'gotoPage' }}({{ $currentPage + 1 }})" class="px-2 md:px-3 py-1 min-w-9 min-h-9 text-xs md:text-sm font-normal text-slate-500 bg-white border border-slate-200 rounded hover:bg-slate-50 hover:border-slate-400 transition duration-200 ease">
            Next
        </button>
    @else
        <button type="button" class="px-2 md:px-3 py-1 min-w-9 min-h-9 text-xs md:text-sm font-normal text-slate-500 bg-white border border-slate-200 rounded cursor-not-allowed opacity-50" disabled>
            Next
        </button>
    @endif
</div>
