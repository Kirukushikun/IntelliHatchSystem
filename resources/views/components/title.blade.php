@props(['subtitle' => ''])

<div class="mb-4 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-bold">
        {{ $slot }}
    </h1>

    @if($subtitle)
        <p class="text-gray-600 dark:text-gray-400 text-sm sm:text-base mt-1">
            {{ $subtitle }}
        </p>
    @endif
</div>
