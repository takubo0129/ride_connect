<nav class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/70 backdrop-blur">
    <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-3 sm:px-6 md:px-8 lg:px-12">
        <div class="flex items-center gap-6">
            <a href="{{ route('home') }}" class="group inline-flex items-center gap-3 rounded-full border border-slate-200/80 bg-white px-3 py-2 text-sm font-semibold tracking-wide text-slate-700 shadow-sm transition hover:border-emerald-300 hover:text-emerald-600">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-500 shadow-inner shadow-emerald-200/30">
                    <img src="{{ asset('images/ride-connect-logo.svg') }}" alt="Ride Connect ロゴ" class="h-6 w-6">
                </span>
                <span class="hidden sm:inline">ライドコネクト</span>
                <span class="sm:hidden">RC</span>
            </a>

            <div class="hidden md:flex items-center gap-1">
                @php
                    $navItems = [
                        ['route' => route('vehicles.index'), 'active' => request()->routeIs('vehicles.index'), 'label' => '車両検索'],
                        ['route' => route('connect.index'), 'active' => request()->routeIs('connect.index'), 'label' => 'コネクト'],
                        ['route' => route('favorites.index'), 'active' => request()->routeIs('favorites.index'), 'label' => 'いいね'],
                    ];
                @endphp
                @foreach ($navItems as $item)
                    <a href="{{ $item['route'] }}"
                       class="inline-flex items-center gap-3 rounded-full px-4 py-2 text-sm font-medium transition {{ $item['active'] ? 'bg-emerald-500/10 text-emerald-700 ring-1 ring-emerald-300' : 'text-slate-600 hover:text-emerald-600 hover:ring-1 hover:ring-emerald-200/60' }}">
                        <span class="h-2 w-2 rounded-full {{ $item['active'] ? 'bg-emerald-500' : 'bg-slate-300/40' }}"></span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="hidden md:inline text-xs font-medium uppercase tracking-[0.24em] text-slate-500">{{ Auth::user()->name ?? '' }}</span>
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-slate-600 transition hover:border-emerald-300 hover:text-emerald-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-200">
                        <span class="sr-only">ユーザーメニュー</span>
                        <img src="{{ asset('images/ride-connect-logo.svg') }}" alt="Ride Connect ロゴ" class="h-6 w-6">
                        <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-500">ショートカット</div>
                    <x-dropdown-link :href="route('profile.edit')">
                        プロフィール設定
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('vehicles.index')">
                        車両検索
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('connect.index')">
                        コネクト
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('favorites.index')">
                        いいね
                    </x-dropdown-link>
                    <div class="my-2 border-t border-slate-200/80"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            ログアウト
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</nav>
