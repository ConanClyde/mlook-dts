@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, danger, success
    'size' => 'md', // sm, md, lg
    'disabled' => false,
    'ariaLabel' => null,
    'ariaDescribedBy' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$variantClasses = match($variant) {
    'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600',
    'secondary' => 'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 dark:bg-red-500 dark:hover:bg-red-600',
    'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 dark:bg-green-500 dark:hover:bg-green-600',
    default => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
};

$sizeClasses = match($size) {
    'sm' => 'px-3 py-2 text-sm min-h-[44px]',
    'md' => 'px-4 py-2.5 text-base min-h-[44px]',
    'lg' => 'px-6 py-3 text-lg min-h-[44px]',
    default => 'px-4 py-2.5 text-base min-h-[44px]',
};

$classes = $baseClasses . ' ' . $variantClasses . ' ' . $sizeClasses;
@endphp

<button
    type="{{ $type }}"
    {{ $disabled ? 'disabled' : '' }}
    @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
    @if($ariaDescribedBy) aria-describedby="{{ $ariaDescribedBy }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</button>
