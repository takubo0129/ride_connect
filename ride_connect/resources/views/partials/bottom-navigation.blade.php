<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200/80 bg-white/90 backdrop-blur">
    <div class="mx-auto max-w-4xl px-4">
        <ul class="grid grid-cols-5 gap-1 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">
            @php
                $items = [
                    [
                        'route' => route('home'),
                        'active' => request()->routeIs('home', 'dashboard'),
                        'icon' => '🏠',
                        'label' => 'ホーム',
                    ],
                    [
                        'route' => route('vehicles.index'),
                        'active' => request()->routeIs('vehicles.index'),
                        'icon' => '🚘',
                        'label' => '検索',
                    ],
                    [
                        'route' => route('connect.index'),
                        'active' => request()->routeIs('connect.index'),
                        'icon' => '🔗',
                        'label' => 'コネクト',
                    ],
                    [
                        'route' => route('favorites.index'),
                        'active' => request()->routeIs('favorites.index'),
                        'icon' => '❤️',
                        'label' => 'いいね',
                    ],
                    [
                        'route' => route('profile.edit'),
                        'active' => request()->routeIs('profile.edit'),
                        'icon' => '⚙️',
                        'label' => '設定',
                    ],
                ];
            @endphp
            @foreach ($items as $item)
                <li>
                    <a href="{{ $item['route'] }}"
                       class="group relative flex flex-col items-center gap-1 rounded-2xl px-3 py-3 transition {{ $item['active'] ? 'bg-emerald-500/10 text-emerald-700 ring-1 ring-emerald-200' : 'text-slate-500 hover:text-emerald-600 hover:ring-1 hover:ring-emerald-200/60' }}">
                        <span class="text-lg transition group-hover:translate-y-[-1px]">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                        @if ($item['active'])
                            <span class="absolute bottom-1 h-1 w-6 rounded-full bg-emerald-400/80"></span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</nav>
