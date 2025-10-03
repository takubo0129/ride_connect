<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <div class="rc-glow-tag">CONNECT</div>
            <h1 class="text-3xl font-semibold text-slate-900 leading-tight">コネクト</h1>
            <p class="text-sm text-slate-500">申請対応から取引完了まで、この画面ひとつで進行状況を把握できます。</p>
        </div>
    </x-slot>

    <div class="rc-shell">
        <div class="mx-auto w-full max-w-5xl space-y-12">
            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('info'))
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    {{ session('info') }}
                </div>
            @endif

            {{-- Pending Requests --}}
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">申請中のリクエスト</h2>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600">{{ $pendingRequests->count() }} 件</span>
                </div>

                @forelse ($pendingRequests as $reservation)
                    @php
                        $vehicle = $reservation->vehicle;
                        $renter = $reservation->renter;
                        $profile = $renter?->profile;
                    @endphp
                    <div class="rc-card p-6 space-y-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start">
                            <div class="space-y-2">
                                <h3 class="text-xl font-semibold text-slate-900">{{ $vehicle->display_maker ?? '車両' }} {{ $vehicle->model ?? '' }}</h3>
                                <div class="flex flex-wrap gap-3 text-sm text-slate-500">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-base">📍</span>
                                        <span>{{ trim(($vehicle->prefecture->name ?? '') . ' ' . ($vehicle->city->name ?? '')) ?: '地域未設定' }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-base">🗓</span>
                                        <span>{{ $reservation->start_date?->isoFormat('YYYY/MM/DD') }} - {{ $reservation->end_date?->isoFormat('YYYY/MM/DD') }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-base">⏱</span>
                                        <span>{{ __('プラン: :period', ['period' => __($reservation->sharing_period ?? '未設定')]) }}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 space-y-1">
                                <p class="font-semibold text-slate-700">借主情報</p>
                                <p>{{ $profile?->nickname ?? $renter?->name ?? 'ユーザー' }}</p>
                                <p>{{ $profile?->region }} {{ $profile?->city }}</p>
                                <a href="{{ route('profile.edit') }}" class="text-emerald-600 hover:text-emerald-500 text-xs">プロフィール詳細を確認</a>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @if ($reservation->notes)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                    <p class="font-semibold text-slate-700 mb-1">借主からのメッセージ</p>
                                    <p>{{ $reservation->notes }}</p>
                                </div>
                            @endif

                            <div class="flex flex-wrap gap-3">
                                <form method="post" action="{{ route('reservations.approve', $reservation) }}">
                                    @csrf
                                    <button type="submit"
                                            class="rc-button-primary"
                                            onclick="return confirm('この予約を承認しますか？');">
                                        承認する
                                    </button>
                                </form>

                                <form method="post" action="{{ route('reservations.reject', $reservation) }}" class="flex items-center gap-2">
                                    @csrf
                                    <input type="text" name="reason" class="rc-input w-60" placeholder="拒否理由（任意）">
                                    <button type="submit"
                                            class="rc-button-secondary text-rose-600 border-rose-500 hover:bg-rose-50"
                                            onclick="return confirm('この予約を拒否しますか？');">
                                        拒否する
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rc-card p-6 text-sm text-slate-500">
                        現在対応すべき予約申請はありません。新しい申請が届くとこちらに表示されます。
                    </div>
                @endforelse
            </section>

            {{-- Owner Active --}}
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">オーナー進行中</h2>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600">{{ $ownerActive->count() }} 件</span>
                </div>

                @forelse ($ownerActive as $reservation)
                    @php
                        $vehicle = $reservation->vehicle;
                        $renter = $reservation->renter;
                        $profile = $renter?->profile;
                        $licenseYearsDisplay = collect($profile?->license_years_meta ?? [])
                            ->map(fn ($years, $license) => trim($license) !== '' && trim((string) $years) !== '' ? $license . ': ' . $years : null)
                            ->filter()
                            ->values()
                            ->implode(' / ');
                        $preCheck = $reservation->pre_check_items['items'] ?? [];
                        $postCheck = $reservation->post_check_items['items'] ?? [];
                        $preNote = $reservation->pre_check_items['note'] ?? null;
                        $postNote = $reservation->post_check_items['note'] ?? null;
                    @endphp
                    <div class="rc-card p-6 space-y-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-slate-900">{{ $vehicle->display_maker ?? '車両' }} {{ $vehicle->model ?? '' }}</h3>
                                <div class="flex flex-wrap gap-3 text-sm text-slate-500 mt-2">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-base">📍</span>
                                        <span>{{ trim(($vehicle->prefecture->name ?? '') . ' ' . ($vehicle->city->name ?? '')) ?: '地域未設定' }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-base">🗓</span>
                                        <span>{{ $reservation->start_date?->isoFormat('YYYY/MM/DD') }} - {{ $reservation->end_date?->isoFormat('YYYY/MM/DD') }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-base">⏱</span>
                                        <span>{{ __('プラン: :period', ['period' => __($reservation->sharing_period ?? '未設定')]) }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-base">📌</span>
                                        <span>{{ __('ステータス: :status', ['status' => $statusLabels[$reservation->status] ?? $reservation->status]) }}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 space-y-1">
                                <p class="font-semibold text-slate-700">借主情報</p>
                                <p>{{ $profile?->nickname ?? $renter?->name ?? 'ユーザー' }}</p>
                                <p>{{ $profile?->region }} {{ $profile?->city }}</p>
                                <p>{{ __('免許歴: :value', ['value' => $licenseYearsDisplay ?: '未設定']) }}</p>
                            </div>
                        </div>

                        @if ($reservation->status === \App\Models\Reservation::STATUS_APPROVED)
                            <div class="space-y-4">
                                <p class="text-sm text-slate-600 font-semibold">乗車前チェック</p>
                                <form method="post" action="{{ route('reservations.pre_check.complete', $reservation) }}" class="space-y-4">
                                    @csrf
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        @foreach ($preCheckItems as $item)
                                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 hover:border-emerald-400">
                                                <input type="checkbox" name="checks[]" value="{{ $item['key'] }}" class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                                                <span>{{ $item['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <textarea name="note" class="rc-input" rows="3" placeholder="特記事項があれば記入してください"></textarea>
                                    <button type="submit" class="rc-button-primary" onclick="return confirm('チェック項目を完了として記録しますか？');">乗車前チェック完了</button>
                                </form>
                            </div>
                        @elseif ($reservation->status === \App\Models\Reservation::STATUS_PRE_CHECK_COMPLETED)
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                乗車前チェックが完了しました。借主がレンタル開始するまでお待ちください。
                            </div>
                        @elseif ($reservation->status === \App\Models\Reservation::STATUS_ENDING_REQUESTED)
                            <div class="space-y-4">
                                <p class="text-sm font-semibold text-slate-700">返却確認</p>
                                <form method="post" action="{{ route('reservations.return.confirm', $reservation) }}" class="flex flex-wrap gap-3">
                                    @csrf
                                    <input type="hidden" name="decision" value="confirmed">
                                    <button type="submit" class="rc-button-primary" onclick="return confirm('車両の返却を確認しますか？');">
                                        返却されました
                                    </button>
                                </form>
                                <form method="post" action="{{ route('reservations.return.confirm', $reservation) }}">
                                    @csrf
                                    <input type="hidden" name="decision" value="waiting">
                                    <button type="submit" class="rc-button-secondary" onclick="return confirm('返却待機中として記録しますか？');">
                                        待機中として記録
                                    </button>
                                </form>
                                @if ($reservation->owner_return_status === 'waiting')
                                    <p class="text-xs text-slate-400">返却を待機中として記録しています。返却完了後に「返却されました」を押してください。</p>
                                @endif
                            </div>
                        @elseif ($reservation->status === \App\Models\Reservation::STATUS_RETURN_CONFIRMED)
                            <div class="space-y-4">
                                <p class="text-sm font-semibold text-slate-700">返却後チェック</p>
                                <form method="post" action="{{ route('reservations.post_check.complete', $reservation) }}" class="space-y-4">
                                    @csrf
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        @foreach ($postCheckItems as $item)
                                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 hover:border-emerald-400">
                                                <input type="checkbox" name="checks[]" value="{{ $item['key'] }}" class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                                                <span>{{ $item['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <textarea name="note" class="rc-input" rows="3" placeholder="特記事項があれば記入してください"></textarea>
                                    <button type="submit" class="rc-button-primary" onclick="return confirm('返却後チェックを完了しますか？');">返却後チェック完了</button>
                                </form>
                            </div>
                        @elseif ($reservation->status === \App\Models\Reservation::STATUS_POST_CHECK_COMPLETED)
                            <div class="space-y-4">
                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                    返却後チェックが完了しました。下記ボタンで取引を終了してください。
                                </div>
                                <form method="post" action="{{ route('reservations.complete', $reservation) }}">
                                    @csrf
                                    <button type="submit" class="rc-button-primary" onclick="return confirm('取引を完了してよろしいですか？');">
                                        取引完了
                                    </button>
                                </form>
                            </div>
                        @endif

                        @if (! empty($preCheck))
                            <div class="space-y-2">
                                <p class="text-sm font-semibold text-slate-700">乗車前チェック記録</p>
                                <ul class="grid gap-2 text-sm text-slate-500 sm:grid-cols-2">
                                    @foreach ($preCheck as $key)
                                        @php
                                            $item = collect($preCheckItems)->firstWhere('key', $key);
                                        @endphp
                                        <li class="rounded-xl border border-slate-200 bg-white px-3 py-2">✅ {{ $item['label'] ?? $key }}</li>
                                    @endforeach
                                </ul>
                                @if ($preNote)
                                    <p class="text-xs text-slate-400">メモ: {{ $preNote }}</p>
                                @endif
                            </div>
                        @endif

                        @if ($reservation->status === \App\Models\Reservation::STATUS_ACTIVE)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                借主がレンタル中です。返却申請が届くまでお待ちください。
                            </div>
                        @endif

                        @if (! empty($postCheck))
                            <div class="space-y-2">
                                <p class="text-sm font-semibold text-slate-700">返却後チェック記録</p>
                                <ul class="grid gap-2 text-sm text-slate-500 sm:grid-cols-2">
                                    @foreach ($postCheck as $key)
                                        @php
                                            $item = collect($postCheckItems)->firstWhere('key', $key);
                                        @endphp
                                        <li class="rounded-xl border border-slate-200 bg-white px-3 py-2">✅ {{ $item['label'] ?? $key }}</li>
                                    @endforeach
                                </ul>
                                @if ($postNote)
                                    <p class="text-xs text-slate-400">メモ: {{ $postNote }}</p>
                                @endif
                            </div>
                        @endif

                        @if ($reservation->isChatEnabled())
                            <div class="space-y-3">
                                <p class="text-sm font-semibold text-slate-700">メッセージ</p>
                                <div class="max-h-60 overflow-y-auto space-y-2 pr-2">
                                    @forelse ($reservation->messages->sortBy('created_at') as $message)
                                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm">
                                            <div class="flex items-center justify-between text-xs text-slate-400">
                                                <span>{{ $message->sender_id === $currentUser->id ? 'あなた' : ($message->sender->profile->nickname ?? $message->sender->name) }}</span>
                                                <span>{{ $message->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="mt-1 text-slate-700 whitespace-pre-line">{{ $message->body }}</p>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-400">まだメッセージはありません。</p>
                                    @endforelse
                                </div>
                                <form method="post" action="{{ route('reservations.messages.store', $reservation) }}" class="flex flex-col gap-2">
                                    @csrf
                                    <textarea name="body" rows="2" class="rc-input" placeholder="メッセージを入力"></textarea>
                                    <button type="submit" class="rc-button-secondary self-end">送信</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rc-card p-6 text-sm text-slate-500">
                        進行中の取引はありません。予約が承認されると表示されます。
                    </div>
                @endforelse
            </section>

            {{-- Renter Active --}}
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">借主の取引状況</h2>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600">{{ $renterActive->count() }} 件</span>
                </div>

                @forelse ($renterActive as $reservation)
                    @php
                        $vehicle = $reservation->vehicle;
                        $owner = $reservation->owner;
                        $profile = $owner?->profile;
                    @endphp
                    <div class="rc-card p-6 space-y-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-slate-900">{{ $vehicle->display_maker ?? '車両' }} {{ $vehicle->model ?? '' }}</h3>
                                <div class="flex flex-wrap gap-3 text-sm text-slate-500 mt-2">
                                    <span class="inline-flex items-center gap-1"><span class="text-base">📍</span>{{ trim(($vehicle->prefecture->name ?? '') . ' ' . ($vehicle->city->name ?? '')) ?: '地域未設定' }}</span>
                                    <span class="inline-flex items-center gap-1"><span class="text-base">🗓</span>{{ $reservation->start_date?->isoFormat('YYYY/MM/DD') }} - {{ $reservation->end_date?->isoFormat('YYYY/MM/DD') }}</span>
                                    <span class="inline-flex items-center gap-1"><span class="text-base">⏱</span>{{ __('プラン: :period', ['period' => __($reservation->sharing_period ?? '未設定')]) }}</span>
                                    <span class="inline-flex items-center gap-1"><span class="text-base">📌</span>{{ __('ステータス: :status', ['status' => $statusLabels[$reservation->status] ?? $reservation->status]) }}</span>
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 space-y-1">
                                <p class="font-semibold text-slate-700">オーナー</p>
                                <p>{{ $profile?->nickname ?? $owner?->name ?? 'オーナー' }}</p>
                                <p>{{ $profile?->region }} {{ $profile?->city }}</p>
                            </div>
                        </div>

                        @if ($reservation->status === \App\Models\Reservation::STATUS_PRE_CHECK_COMPLETED)
                            <form method="post" action="{{ route('reservations.start', $reservation) }}" class="flex flex-wrap gap-3">
                                @csrf
                                <button type="submit" class="rc-button-primary" onclick="return confirm('安全運転でブリーフィング内容に従いますか？');">
                                    レンタル開始
                                </button>
                            </form>
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                オーナーの事前チェックが完了しました。受け渡し後に「レンタル開始」を押してください。
                            </div>
                        @endif

                        @if ($reservation->status === \App\Models\Reservation::STATUS_ACTIVE)
                            <div class="grid gap-4 md:grid-cols-[1fr,auto]">
                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                                    <p class="text-sm font-semibold text-emerald-700">利用中の経過時間</p>
                                    <p class="text-3xl font-semibold text-emerald-600 mt-2 js-stopwatch" data-start="{{ optional($reservation->ride_started_at)->toIso8601String() }}">--:--:--</p>
                                </div>
                                <div class="space-y-3">
                                    <form method="post" action="{{ route('reservations.end', $reservation) }}">
                                        @csrf
                                        <button type="submit" class="rc-button-secondary" onclick="return confirm('返却手続きを開始しますか？');">
                                            レンタル終了
                                        </button>
                                    </form>
                                    <a href="mailto:{{ $owner->email }}" class="rc-button-secondary block text-center">オーナーに連絡</a>
                                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                                        <p class="font-semibold text-slate-700 mb-2">Q&A</p>
                                        <ul class="space-y-1">
                                            @foreach ($qaLinks as $link)
                                                <li><a href="{{ $link['url'] }}" class="text-emerald-600 hover:text-emerald-500">{{ $link['label'] }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @elseif ($reservation->status === \App\Models\Reservation::STATUS_ENDING_REQUESTED)
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                返却申請を送信しました。オーナーの確認後に取引が進行します。
                            </div>
                        @endif

                        @if ($reservation->isChatEnabled())
                            <div class="space-y-3">
                                <p class="text-sm font-semibold text-slate-700">メッセージ</p>
                                <div class="max-h-60 overflow-y-auto space-y-2 pr-2">
                                    @forelse ($reservation->messages->sortBy('created_at') as $message)
                                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm">
                                            <div class="flex items-center justify-between text-xs text-slate-400">
                                                <span>{{ $message->sender_id === $currentUser->id ? 'あなた' : ($message->sender->profile->nickname ?? $message->sender->name) }}</span>
                                                <span>{{ $message->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="mt-1 text-slate-700 whitespace-pre-line">{{ $message->body }}</p>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-400">まだメッセージはありません。</p>
                                    @endforelse
                                </div>
                                <form method="post" action="{{ route('reservations.messages.store', $reservation) }}" class="flex flex-col gap-2">
                                    @csrf
                                    <textarea name="body" rows="2" class="rc-input" placeholder="メッセージを入力"></textarea>
                                    <button type="submit" class="rc-button-secondary self-end">送信</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rc-card p-6 text-sm text-slate-500">
                        進行中のレンタルはありません。予約申請が承認されると表示されます。
                    </div>
                @endforelse
            </section>

            {{-- History --}}
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">直近の取引履歴</h2>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    @forelse ($history as $reservation)
                        @php
                            $vehicle = $reservation->vehicle;
                            $partner = $reservation->isOwner($currentUser->id) ? $reservation->renter : $reservation->owner;
                            $profile = $partner?->profile;
                        @endphp
                        <div class="rc-card p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-semibold text-slate-900">{{ $vehicle->display_maker ?? '車両' }} {{ $vehicle->model ?? '' }}</h3>
                                <span class="text-xs text-slate-400">{{ $reservation->completed_at?->isoFormat('YYYY/MM/DD') }}</span>
                            </div>
                            <p class="text-sm text-slate-500">{{ $reservation->start_date?->isoFormat('YYYY/MM/DD') }} - {{ $reservation->end_date?->isoFormat('YYYY/MM/DD') }}</p>
                            <p class="text-xs text-slate-400">相手: {{ $profile?->nickname ?? $partner?->name ?? 'ユーザー' }}</p>
                            <span class="inline-flex w-fit items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                {{ $statusLabels[$reservation->status] ?? strtoupper($reservation->status) }}
                            </span>
                        </div>
                    @empty
                        <div class="rc-card p-6 text-sm text-slate-500">
                            まだ取引履歴はありません。
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    @include('partials.bottom-navigation')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const format = (ms) => {
                    const totalSeconds = Math.floor(ms / 1000);
                    const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
                    const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
                    const seconds = String(totalSeconds % 60).padStart(2, '0');
                    return `${hours}:${minutes}:${seconds}`;
                };

                document.querySelectorAll('.js-stopwatch').forEach((element) => {
                    const start = element.dataset.start;
                    if (!start) return;
                    const startTime = new Date(start).getTime();
                    const tick = () => {
                        const now = Date.now();
                        const diff = now - startTime;
                        if (diff >= 0) {
                            element.textContent = format(diff);
                        }
                    };
                    tick();
                    setInterval(tick, 1000);
                });
            });
        </script>
    @endpush
</x-app-layout>
