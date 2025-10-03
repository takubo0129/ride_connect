@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-2xl border border-emerald-400/40 bg-emerald-500/15 px-4 py-2 text-start text-sm font-semibold uppercase tracking-wide text-white shadow-[0_16px_40px_-22px_rgba(16,185,129,0.7)] transition focus:outline-none focus:ring-2 focus:ring-emerald-300/40'
            : 'block w-full rounded-2xl border border-transparent px-4 py-2 text-start text-sm font-semibold uppercase tracking-wide text-emerald-100 hover:border-emerald-200/30 hover:bg-white/5 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-white/10';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
