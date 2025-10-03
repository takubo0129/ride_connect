<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-emerald-400 via-emerald-500 to-teal-400 px-5 py-2 text-xs font-semibold uppercase tracking-wide text-slate-950 shadow-[0_16px_45px_-18px_rgba(16,185,129,0.7)] transition hover:scale-[1.03] hover:shadow-[0_22px_60px_-20px_rgba(16,185,129,0.75)] focus:outline-none focus:ring-2 focus:ring-emerald-400/40']) }}>
    {{ $slot }}
</button>
