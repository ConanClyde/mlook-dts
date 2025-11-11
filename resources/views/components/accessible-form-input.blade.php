@props([
    'id',
    'name',
    'type' => 'text',
    'label',
    'required' => false,
    'error' => null,
    'helpText' => null,
    'value' => '',
    'placeholder' => '',
])

@php
$inputId = $id ?? $name;
$errorId = $inputId . '-error';
$helpId = $inputId . '-help';
@endphp

<div class="space-y-1">
    @if($label)
        <label 
            for="{{ $inputId }}" 
            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
        >
            {{ $label }}
            @if($required)
                <span class="text-red-500" aria-label="required">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $inputId }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        @if($error) aria-invalid="true" aria-describedby="{{ $errorId }}" @endif
        @if($helpText && !$error) aria-describedby="{{ $helpId }}" @endif
        {{ $attributes->merge([
            'class' => 'block w-full px-3 py-2.5 min-h-[44px] rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white ' . ($error ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : '')
        ]) }}
    >

    @if($helpText && !$error)
        <p id="{{ $helpId }}" class="text-sm text-gray-500 dark:text-gray-400">
            {{ $helpText }}
        </p>
    @endif

    @if($error)
        <p id="{{ $errorId }}" class="text-sm text-red-600 dark:text-red-400" role="alert">
            {{ $error }}
        </p>
    @endif
</div>
