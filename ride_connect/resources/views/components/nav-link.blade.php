@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center gap-2 rounded-full bg-emerald-400/20 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-[0_12px_40px_-20px_rgba(16,185,129,0.6)] transition focus:outline-none focus:ring-2 focus:ring-emerald-400/40'
            : 'inline-flex items-center gap-2 rounded-full px-3 py-2 text-xs font-semibold uppercase tracking-wide text-emerald-100 hover:bg-white/10 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-white/10';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
