@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-2xl border border-white/30 bg-white/70 px-4 py-2 text-sm text-slate-900 shadow-inner shadow-white/10 focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-400/20 disabled:opacity-60']) }}>
