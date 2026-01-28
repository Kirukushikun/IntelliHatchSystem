@props(['subtitle' => ''])

<div class="mb-6">
    <h1 class="text-2xl font-bold">
        {{ $slot }}
    </h1>

    @if($subtitle)
        <p class="text-gray-600 mt-1">
            {{ $subtitle }}
        </p>
    @endif
</div>
