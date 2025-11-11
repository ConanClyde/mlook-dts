@props(['name', 'type' => 'outline', 'class' => 'w-6 h-6'])

@php
    $iconPath = base_path("node_modules/heroicons/24/{$type}/{$name}.svg");
    $svg = file_exists($iconPath) ? file_get_contents($iconPath) : '';
@endphp

{!! str_replace('<svg', '<svg class="' . $class . '"', $svg) !!}

