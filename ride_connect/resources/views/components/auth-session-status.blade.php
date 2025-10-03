@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-emerald-200']) }}>
        {{ $status }}
    </div>
@endif
