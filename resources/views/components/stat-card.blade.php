{{-- resources/views/components/stat-card.blade.php --}}
@props([
    'title',
    'value',
    'icon',
    'color' => 'blue',
])

@php
    $borderColor = match($color) {
        'blue'   => 'border-blue-500',
        'green'  => 'border-green-500',
        'red'    => 'border-red-500',
        'yellow' => 'border-yellow-500',
        default  => 'border-gray-500',
    };

    $iconColor = match($color) {
        'blue'   => 'text-blue-500 bg-blue-50',
        'green'  => 'text-green-500 bg-green-50',
        'red'    => 'text-red-500 bg-red-50',
        'yellow' => 'text-yellow-500 bg-yellow-50',
        default  => 'text-gray-500 bg-gray-50',
    };
@endphp

<div {{ $attributes->merge(['class' => "bg-white rounded-lg shadow border-l-4 {$borderColor} p-5"]) }}>
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">{{ $title }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $value }}</p>
        </div>
        <div class="p-3 rounded-full {{ $iconColor }}">
            {!! $icon !!}
        </div>
    </div>
</div>
