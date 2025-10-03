@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Storage;

    $user = Auth::user();
    $nickname = $user?->profile?->nickname ?: ($user?->name ?: 'ゲスト');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <div class="rc-glow-tag">HOME</div>
            <h1 class="text-3xl font-semibold leading-tight text-slate-900">ホーム</h1>
            <p class="text-sm text-slate-500">
                <span class="font-semibold text-slate-900">{{ $nickname }}さん、こんにちは。</span>
                ようこそ、ライドコネクトへ。最新のモビリティ情報とあなたのコレクションをまとめてチェックできます。
            </p>
        </div>
    </x-slot>

    <div class="rc-shell">
        <section class="space-y-6">
            @if ($needsProfile ?? false)
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50/80 p-6 sm:p-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-emerald-700">プロフィール設定がまだ完了していません</p>
                            <p class="text-sm text-emerald-600">スムーズな取引のために、基本情報を登録しましょう。</p>
                        </div>
                        <a href="{{ route('onboarding.profile') }}" class="rc-button-primary">プロフィール設定へ</a>
                    </div>
                </div>
            @endif

            <form action="{{ route('vehicles.index') }}" method="GET" class="relative max-w-xl">
                <input
                    type="search"
                    name="q"
                    placeholder="キーワードや地域で車・バイクを検索"
                    class="rc-input pr-12"
                >
                <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-lg text-emerald-500 transition hover:text-emerald-600">
                    🔍
                </button>
            </form>
        </section>

        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-900">クイックアクセス</h2>
                <span class="text-xs font-semibold uppercase tracking-[0.32em] text-slate-400">よく使う機能</span>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <a href="{{ route('vehicles.index') }}"
                   class="group flex h-full flex-col justify-between rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                    <div class="space-y-3">
                        <span class="text-2xl">🔎</span>
                        <h3 class="text-lg font-semibold text-slate-900">車両検索（すべて）</h3>
                        <p class="text-sm leading-relaxed text-slate-500">条件を指定せずに最新の車・バイクを一覧でチェックできます。</p>
                    </div>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-emerald-600">検索ページへ →</span>
                </a>

                <a href="{{ route('vehicles.index', ['type' => 'car']) }}"
                   class="group flex h-full flex-col justify-between rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                    <div class="space-y-3">
                        <span class="text-2xl">🚗</span>
                        <h3 class="text-lg font-semibold text-slate-900">車を探す</h3>
                        <p class="text-sm leading-relaxed text-slate-500">ボディタイプや乗車定員から理想の車を見つけましょう。</p>
                    </div>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-emerald-600">車の検索 →</span>
                </a>

                <a href="{{ route('vehicles.index', ['type' => 'bike']) }}"
                   class="group flex h-full flex-col justify-between rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                    <div class="space-y-3">
                        <span class="text-2xl">🏍️</span>
                        <h3 class="text-lg font-semibold text-slate-900">バイクを探す</h3>
                        <p class="text-sm leading-relaxed text-slate-500">排気量カテゴリやシート高でバイク検索が可能です。</p>
                    </div>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-emerald-600">バイクの検索 →</span>
                </a>

                <a href="{{ route('vehicles.create') }}"
                   class="group flex h-full flex-col justify-between rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                    <div class="space-y-3">
                        <span class="text-2xl">➕</span>
                        <h3 class="text-lg font-semibold text-slate-900">車両を登録する</h3>
                        <p class="text-sm leading-relaxed text-slate-500">ステップ形式のフォームで簡単に掲載できます。</p>
                    </div>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-emerald-600">登録ページへ →</span>
                </a>

                <a href="{{ route('connect.index') }}"
                   class="group flex h-full flex-col justify-between rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                    <div class="space-y-3">
                        <span class="text-2xl">🔗</span>
                        <h3 class="text-lg font-semibold text-slate-900">コネクト</h3>
                        <p class="text-sm leading-relaxed text-slate-500">相性の良いオーナーとのつながりを見つけましょう。</p>
                    </div>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-emerald-600">コネクトへ →</span>
                </a>

                <a href="{{ route('favorites.index') }}"
                   class="group flex h-full flex-col justify-between rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                    <div class="space-y-3">
                        <span class="text-2xl">❤️</span>
                        <h3 class="text-lg font-semibold text-slate-900">いいね一覧</h3>
                        <p class="text-sm leading-relaxed text-slate-500">気になる車両をすぐに再チェックできます。</p>
                    </div>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-emerald-600">お気に入りへ →</span>
                </a>

                <a href="{{ route('profile.edit') }}"
                   class="group flex h-full flex-col justify-between rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                    <div class="space-y-3">
                        <span class="text-2xl">🧑‍💼</span>
                        <h3 class="text-lg font-semibold text-slate-900">プロフィール編集</h3>
                        <p class="text-sm leading-relaxed text-slate-500">ニックネームや自己紹介を更新して信頼度をアピールしましょう。</p>
                    </div>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-emerald-600">プロフィール →</span>
                </a>

                <a href="{{ route('onboarding.profile') }}"
                   class="group flex h-full flex-col justify-between rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                    <div class="space-y-3">
                        <span class="text-2xl">📝</span>
                        <h3 class="text-lg font-semibold text-slate-900">オンボーディング</h3>
                        <p class="text-sm leading-relaxed text-slate-500">初期設定を見直して、よりスムーズな取引体験に。</p>
                    </div>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-emerald-600">設定を再確認 →</span>
                </a>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-900">最新の掲載車両</h2>
                <a href="{{ route('vehicles.index') }}" class="text-sm font-semibold text-emerald-600">すべて表示</a>
            </div>

            @if ($latestVehicles->isEmpty())
                <div class="rounded-3xl border-2 border-dashed border-slate-300/80 bg-white/80 p-10 text-center text-slate-500">
                    まだ車両が登録されていません。最初の1台を登録してみましょう。
                    <div class="mt-4">
                        <a href="{{ route('vehicles.create') }}" class="rc-button-secondary">車両を登録する</a>
                    </div>
                </div>
            @else
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($latestVehicles as $vehicle)
                        @php
                            $firstImage = is_array($vehicle->images) && count($vehicle->images) ? Storage::url($vehicle->images[0]) : null;
                            $location = trim(($vehicle->prefecture->name ?? '') . ' ' . ($vehicle->city->name ?? ''));
                        @endphp
                        <a href="{{ route('vehicles.show', $vehicle) }}" class="group flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                            <div class="relative h-44 bg-slate-100">
                                @if ($firstImage)
                                    <img src="{{ $firstImage }}" alt="{{ $vehicle->model }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-slate-500">写真は準備中です</div>
                                @endif
                                <span class="absolute left-4 top-4 inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-emerald-700 shadow-sm">{{ $vehicle->type === 'car' ? '車' : 'バイク' }}</span>
                            </div>
                            <div class="flex flex-1 flex-col gap-3 p-6">
                                <div class="space-y-1">
                                    <p class="text-xs uppercase tracking-[0.26em] text-slate-400">{{ $location ?: '地域未設定' }}</p>
                                    <h3 class="text-lg font-semibold text-slate-900 transition group-hover:text-emerald-600">
                                        {{ $vehicle->display_maker }} {{ $vehicle->model }}
                                    </h3>
                                </div>
                                <div class="mt-auto flex items-center justify-between text-sm text-slate-500">
                                    <span>{{ $vehicle->year ? $vehicle->year . '年式' : '年式不明' }}</span>
                                    <span class="text-base font-semibold text-emerald-600">¥{{ number_format($vehicle->price_per_day) }} / 日</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-900">あなたの登録車両</h2>
                <a href="{{ route('vehicles.create') }}" class="text-sm font-semibold text-emerald-600">新しい車両を登録</a>
            </div>

            @if ($userVehicles->isEmpty())
                <div class="rounded-3xl border-2 border-dashed border-slate-300/80 bg-white/80 p-8 text-center text-slate-500">
                    まだ車両を登録していません。まずは所有車両を掲載してみましょう。
                    <div class="mt-4">
                        <a href="{{ route('vehicles.create') }}" class="rc-button-secondary">掲載を始める</a>
                    </div>
                </div>
            @else
                <div class="grid gap-6 sm:grid-cols-2">
                    @foreach ($userVehicles as $vehicle)
                        @php
                            $thumb = is_array($vehicle->images) && count($vehicle->images) ? Storage::url($vehicle->images[0]) : null;
                            $location = trim(($vehicle->prefecture->name ?? '') . ' ' . ($vehicle->city->name ?? ''));
                        @endphp
                        <div class="flex overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                            <div class="h-32 w-32 flex-shrink-0 bg-slate-100">
                                @if ($thumb)
                                    <img src="{{ $thumb }}" alt="{{ $vehicle->model }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-xs text-slate-500">写真なし</div>
                                @endif
                            </div>
                            <div class="flex flex-1 flex-col justify-between p-4 text-sm">
                                <div class="space-y-1">
                                    <h3 class="font-semibold text-slate-900">{{ $vehicle->display_maker }} {{ $vehicle->model }}</h3>
                                    <p class="text-slate-500">{{ $location ?: '地域未設定' }}</p>
                                </div>
                                <div class="flex flex-wrap items-center justify-between gap-3 text-slate-500">
                                    <span>{{ $vehicle->sharing_periods ? count($vehicle->sharing_periods) . 'プラン' : '期間未設定' }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-emerald-600">¥{{ number_format($vehicle->price_per_day) }}/日</span>
                                        <a href="{{ route('vehicles.edit', $vehicle) }}"
                                           class="inline-flex items-center gap-1 rounded-full border border-emerald-300 px-3 py-1 text-xs font-semibold text-emerald-600 transition hover:border-emerald-400 hover:text-emerald-700">
                                            編集
                                        </a>
                                        <a href="{{ route('vehicles.show', $vehicle) }}"
                                           class="text-xs font-semibold text-emerald-500 transition hover:text-emerald-600">
                                            詳細
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-semibold text-slate-900">サポート & リソース</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rc-card p-6">
                    <h3 class="text-lg font-semibold text-slate-900">ガイドライン</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">走行時の注意点や受け渡しのポイントを再確認して、安心・安全なシェアリング体験を。</p>
                    <ul class="mt-4 space-y-2 text-sm text-emerald-600">
                        <li>・プロフィール情報の充実で信頼度アップ</li>
                        <li>・受け渡し前の車両チェックを徹底</li>
                        <li>・レビュー機能で次の利用者にアピール</li>
                    </ul>
                </div>
                <div class="rc-card p-6">
                    <h3 class="text-lg font-semibold text-slate-900">ヘルプ</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">ご不明点があれば、お問い合わせフォームからご連絡ください。開発中の機能についてもフィードバックをお待ちしています。</p>
                    <div class="mt-4">
                        <a href="mailto:support@example.com" class="rc-button-secondary">✉️ サポートに連絡</a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @include('partials.bottom-navigation')
</x-app-layout>
