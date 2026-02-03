@props(['class' => ''])

<div {{ $attributes->merge(['class' => $class]) }}>
    <livewire:auth.change-password />
</div>
