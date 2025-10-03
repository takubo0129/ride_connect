@props(['messages'])

@php
    $messages = collect($messages)->flatten()->filter()->all();
@endphp

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-rose-200 space-y-1']) }}>
        @foreach ($messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
