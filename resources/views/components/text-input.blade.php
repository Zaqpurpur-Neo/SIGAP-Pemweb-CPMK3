@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' => 'block w-full border border-slate-400 bg-white text-gray-900 rounded-lg px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-colors duration-200 placeholder-gray-400'
]) !!}>
