<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 rounded-full border border-emerald-200 bg-transparent px-5 py-2 text-xs font-semibold uppercase tracking-wide text-emerald-100 shadow-[0_12px_40px_-24px_rgba(56,189,248,0.6)] transition hover:border-emerald-100 hover:text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-emerald-300/30 disabled:opacity-40']) }}>
    {{ $slot }}
</button>
