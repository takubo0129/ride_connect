@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-xs uppercase tracking-wide text-emerald-100/80']) }}>
    {{ $value ?? $slot }}
</label>
