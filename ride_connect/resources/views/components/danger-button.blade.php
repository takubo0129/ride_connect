<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-rose-500 via-rose-600 to-red-500 px-5 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-[0_18px_45px_-20px_rgba(244,63,94,0.75)] transition hover:scale-[1.03] hover:shadow-[0_22px_60px_-18px_rgba(244,63,94,0.85)] focus:outline-none focus:ring-2 focus:ring-rose-400/40']) }}>
    {{ $slot }}
</button>
