<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ url()->previous() === url()->current() ? route('vehicles.index') : url()->previous() }}"
               class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 text-slate-500 transition hover:border-emerald-400 hover:text-emerald-500">
                <span class="sr-only">前の画面に戻る</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-semibold text-slate-900">{{ $vehicle->model }}</h2>
                <p class="text-sm text-slate-500">{{ $vehicle->prefecture->name ?? '' }}{{ $vehicle->city ? '・' . $vehicle->city->name : '' }} / {{ $vehicle->display_maker }}</p>
            </div>
        </div>
    </x-slot>

    <div class="bg-slate-50 py-10">
        <div class="max-w-6xl mx-auto px-4 space-y-8">
            @if (session('vehicle_registered_message'))
                <div class="flex flex-col gap-3 rounded-3xl border border-emerald-300 bg-white px-6 py-5 shadow"
                     x-data="{ visible: true }" x-show="visible" x-transition>
                    <div class="flex items-center gap-3 text-emerald-700">
                        <span class="text-xl">🎉</span>
                        <div class="text-left">
                            <p class="text-sm font-semibold">車両登録が完了しました！</p>
                            <p class="text-xs text-emerald-500">{{ session('vehicle_registered_message') }}</p>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" @click="visible = false"
                                class="rounded-xl border border-emerald-200 px-4 py-1 text-xs text-emerald-600 hover:border-emerald-300 hover:bg-emerald-50">
                            閉じる
                        </button>
                    </div>
                </div>
            @endif

            @if (session('vehicle_updated_message'))
                <div class="flex flex-col gap-3 rounded-3xl border border-emerald-200 bg-emerald-50 px-6 py-5 text-emerald-700">
                    <div class="flex items-center gap-2 text-sm font-semibold">
                        <span>✅</span>
                        <span>{{ session('vehicle_updated_message') }}</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('home') }}" class="rc-button-primary">ホームに戻る</a>
                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="rc-button-secondary">さらに編集する</a>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="flex flex-col gap-3 rounded-3xl border border-emerald-200 bg-emerald-50 px-6 py-5 text-emerald-700">
                    <div class="text-sm font-semibold">{{ session('success') }}</div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('home') }}" class="rc-button-primary">ホームに戻る</a>
                        <a href="{{ route('vehicles.index') }}" class="rc-button-secondary">車両検索へ</a>
                    </div>
                </div>
            @endif

            @if (session('reservation_success'))
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-emerald-700">
                    {{ session('reservation_success') }}
                </div>
            @endif

            <div class="grid gap-8 lg:grid-cols-[2fr,1fr]">
                <div class="space-y-8">
                    @if (count($imageUrls))
                        <div
                            x-data="vehicleGallery({ images: {{ json_encode($imageUrls, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }} })"
                            class="overflow-hidden rounded-3xl border border-slate-300 bg-white shadow-xl"
                        >
                            <div class="relative aspect-[4/3] bg-slate-100">
                                <template x-if="images.length">
                                    <img :src="current" alt="{{ $vehicle->model }}"
                                         class="h-full w-full object-cover">
                                </template>

                                <button type="button" @click="prev"
                                        class="absolute top-1/2 left-3 -translate-y-1/2 rounded-full bg-white/70 p-2 text-slate-600 shadow hover:bg-white"
                                        x-show="images.length > 1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                    </svg>
                                </button>
                                <button type="button" @click="next"
                                        class="absolute top-1/2 right-3 -translate-y-1/2 rounded-full bg-white/70 p-2 text-slate-600 shadow hover:bg-white"
                                        x-show="images.length > 1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>

                                <template x-if="images.length > 1">
                                    <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-2 rounded-full bg-black/40 px-4 py-1 text-xs text-white">
                                        <template x-for="(item, index) in images" :key="item">
                                            <button type="button" @click="setActive(index)"
                                                    class="h-2 w-2 rounded-full"
                                                    :class="active === index ? 'bg-white' : 'bg-white/40'"></button>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <template x-if="images.length > 1">
                                <div class="grid grid-cols-4 gap-3 bg-white p-4">
                                    <template x-for="(item, index) in images" :key="item">
                                        <button type="button" @click="setActive(index)"
                                                class="relative overflow-hidden rounded-2xl border"
                                                :class="active === index ? 'border-emerald-400' : 'border-transparent'">
                                            <img :src="item" alt="サムネイル"
                                                 class="h-20 w-full object-cover">
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    @else
                        <div class="flex aspect-[4/3] items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-white text-slate-400">
                            写真はまだ追加されていません
                        </div>
                    @endif

                    <div class="rounded-3xl border border-slate-300 bg-white p-8 shadow-xl">
                        <h3 class="text-lg font-semibold text-slate-900">車両情報</h3>
                        <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                            @foreach ($specs as $spec)
                                <div class="rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3">
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $spec['label'] }}</dt>
                                    <dd class="mt-1 text-sm text-slate-700">{{ $spec['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>

                    @if ($vehicle->description)
                        <div class="rounded-3xl border border-slate-300 bg-white p-8 shadow-xl">
                            <h3 class="text-lg font-semibold text-slate-900">オーナーからのコメント</h3>
                            <p class="mt-4 whitespace-pre-wrap text-sm leading-relaxed text-slate-700">{{ $vehicle->description }}</p>
                        </div>
                    @endif

                    @if ($cautionSections->count())
                        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-8 shadow-xl">
                            <h3 class="text-lg font-semibold text-amber-900">シェアリング時の注意事項</h3>
                            <dl class="mt-6 space-y-4">
                                @foreach ($cautionSections as $section)
                                    <div>
                                        <dt class="text-sm font-semibold text-amber-800">{{ $section['label'] }}</dt>
                                        <dd class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-amber-900">{{ $section['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endif
                </div>

                <div class="space-y-6">
                    <div class="rounded-3xl border border-slate-300 bg-white p-8 shadow-xl">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">レンタル料金</div>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-3xl font-semibold text-slate-900">¥{{ number_format($vehicle->price_per_day) }}</span>
                                    <span class="text-sm text-slate-500">/ 日</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($sharingPeriods->count())
                                    <div class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600 whitespace-nowrap">
                                        選べる期間 {{ $sharingPeriods->count() }} 件
                                    </div>
                                @endif

                                @auth
                                    <div x-data="favoriteToggle({
                                            initial: {{ json_encode($isFavorited) }},
                                            count: {{ $favoritesCount }},
                                            toggleUrl: '{{ route('favorites.toggle', $vehicle) }}',
                                            token: '{{ csrf_token() }}'
                                        })" class="text-right">
                                        <button type="button" @click.prevent="toggle" :disabled="processing"
                                                class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-xs font-semibold transition"
                                                :class="favorited ? 'border-emerald-400 bg-emerald-50 text-emerald-700 shadow-inner' : 'border-slate-300 bg-slate-50 text-slate-600 hover:border-emerald-300 hover:text-emerald-500'">
                                            <span x-text="favorited ? '❤️' : '🤍'"></span>
                                            <span x-text="favorited ? 'いいね済み' : 'いいね'" class="whitespace-nowrap"></span>
                                        </button>
                                        <div class="mt-1 text-[11px] text-slate-500">
                                            <span x-text="count"></span> 件がこの車両をお気に入り
                                        </div>
                                        <p x-show="error" x-text="error" class="mt-1 text-[11px] text-rose-500"></p>
                                    </div>
                                @else
                                    <a href="{{ route('login') }}" class="rounded-full border border-emerald-400 px-3 py-2 text-xs font-semibold text-emerald-300 transition hover:bg-emerald-400 hover:text-slate-900 whitespace-nowrap">
                                        ❤️ ログインしていいね
                                    </a>
                                @endauth
                            </div>
                        </div>

                        <div class="mt-6 space-y-4 text-sm text-slate-600">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8.25c0-1.493 0-2.24.29-2.822a3 3 0 0 1 1.311-1.311C5.182 3.75 5.93 3.75 7.425 3.75h9.15c1.494 0 2.24 0 2.822.29a3 3 0 0 1 1.311 1.311c.29.582.29 1.33.29 2.824v7.5c0 1.494 0 2.24-.29 2.822a3 3 0 0 1-1.311 1.311c-.582.29-1.328.29-2.822.29h-9.15c-1.494 0-2.241 0-2.823-.29a3 3 0 0 1-1.31-1.311C3 18 3 17.254 3 15.76V8.25Z" />
                                </svg>
                                <span>{{ $vehicle->prefecture->name ?? '都道府県未設定' }}{{ $vehicle->city ? '・' . $vehicle->city->name : '' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 20.106a7.5 7.5 0 0 1 15 0A17.933 17.933 0 0 1 12 21.75c-2.651 0-5.18-.584-7.5-1.644Z" />
                                </svg>
                                <span>オーナー：{{ optional($vehicle->user)->name ?? '非公開' }}</span>
                            </div>
                        </div>

                        @if ($sharingPeriods->count())
                            <div class="mt-6">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">選択可能なシェアリング期間</div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($sharingPeriods as $period)
                                        <span class="rounded-full border border-slate-300 bg-slate-50 px-3 py-1 text-xs text-slate-600">
                                            {{ $period['label'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-3xl border border-slate-300 bg-white p-8 shadow-xl">
                        <h3 class="text-lg font-semibold text-slate-900">予約申請</h3>
                        <p class="mt-2 text-sm text-slate-500">希望日程とシェアリング期間を選択して予約申請を送信しましょう。</p>

                        @auth
                            @if ($sharingPeriods->isEmpty())
                                <p class="mt-6 rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                    申し訳ありません。この車両は現在予約可能なシェアリング期間が未設定のため、申請を行えません。オーナーが期間を更新するまでお待ちください。
                                </p>
                            @else
                            <form method="POST" action="{{ route('vehicles.reservations.store', $vehicle) }}"
                                  x-data="reservationForm({
                                      periods: {{ json_encode($sharingPeriods, JSON_UNESCAPED_UNICODE) }},
                                      initialPeriod: '{{ old('sharing_period') }}',
                                      startDate: '{{ old('start_date') }}',
                                      notes: {{ json_encode(old('notes')) }}
                                  })"
                                  class="mt-6 space-y-6">
                                @csrf
                                <div class="space-y-2">
                                    <label for="start_date" class="text-sm font-medium text-slate-700">利用開始日</label>
                                    <input type="date" id="start_date" name="start_date" x-model="startDate"
                                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-emerald-400 focus:outline-none focus:ring-0"
                                           min="{{ now()->format('Y-m-d') }}">
                                    <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                                </div>

                                <div class="space-y-2">
                                    <span class="text-sm font-medium text-slate-700">シェアリング期間</span>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="period in periods" :key="period.key">
                                            <button type="button" @click="selectPeriod(period.key)"
                                                    class="rounded-full border px-4 py-2 text-xs transition"
                                                    :class="isActive(period.key) ? 'border-emerald-400 bg-emerald-50 text-emerald-700 shadow-inner' : 'border-slate-300 bg-slate-50 text-slate-600 hover:bg-slate-100'">
                                                <span x-text="period.label"></span>
                                            </button>
                                        </template>
                                    </div>
                                    <input type="hidden" name="sharing_period" :value="selectedPeriod">
                                    <x-input-error class="mt-2" :messages="$errors->get('sharing_period')" />
                                </div>

                                <div class="space-y-2">
                                    <label for="notes" class="text-sm font-medium text-slate-700">備考（任意）</label>
                                    <textarea id="notes" name="notes" rows="3" x-model="notes"
                                              class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-emerald-400 focus:outline-none focus:ring-0"
                                              placeholder="受け渡し希望時間や注意事項があれば入力してください。"></textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                                </div>

                                <div class="rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                    <p class="flex items-center gap-2 text-slate-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75 11.25 15l3.75-4.5" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        選択内容
                                    </p>
                                    <dl class="mt-3 space-y-1">
                                        <div class="flex justify-between text-xs text-slate-500">
                                            <dt>利用開始日</dt>
                                            <dd x-text="startDate || '未選択'" class="font-medium text-slate-700"></dd>
                                        </div>
                                        <div class="flex justify-between text-xs text-slate-500">
                                            <dt>終了予定日</dt>
                                            <dd x-text="endDate || '未選択'" class="font-medium text-slate-700"></dd>
                                        </div>
                                        <div class="flex justify-between text-xs text-slate-500">
                                            <dt>期間</dt>
                                            <dd x-text="activePeriodLabel || '未選択'" class="font-medium text-slate-700"></dd>
                                        </div>
                                    </dl>
                                </div>

                                <button type="submit"
                                        class="w-full rounded-2xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:ring-offset-2">
                                    予約申請を送信する
                                </button>
                            </form>
                            @endif
                        @else
                            <div class="mt-6 space-y-4">
                                <p class="rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                    予約申請を行うにはログインが必要です。アカウントをお持ちでない場合は会員登録をお願いします。
                                </p>
                                <div class="flex flex-col gap-3 sm:flex-row">
                                    <a href="{{ route('login') }}" class="flex-1 rounded-2xl border border-emerald-500 px-4 py-3 text-center text-sm font-semibold text-emerald-600 transition hover:bg-emerald-50">ログイン</a>
                                    <a href="{{ route('register') }}" class="flex-1 rounded-2xl bg-emerald-500 px-4 py-3 text-center text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-600">新規登録</a>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.bottom-navigation')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('vehicleGallery', (config) => ({
                images: Array.isArray(config.images) ? config.images : [],
                active: 0,
                get current() {
                    return this.images[this.active] ?? '';
                },
                setActive(index) {
                    if (index >= 0 && index < this.images.length) {
                        this.active = index;
                    }
                },
                prev() {
                    if (! this.images.length) {
                        return;
                    }
                    this.active = (this.active - 1 + this.images.length) % this.images.length;
                },
                next() {
                    if (! this.images.length) {
                        return;
                    }
                    this.active = (this.active + 1) % this.images.length;
                },
            }));

            Alpine.data('reservationForm', (config) => ({
                periods: Array.isArray(config.periods) ? config.periods : [],
                selectedPeriod: config.initialPeriod || (config.periods?.[0]?.key ?? ''),
                startDate: config.startDate || '',
                notes: config.notes || '',
                selectPeriod(key) {
                    this.selectedPeriod = key;
                },
                isActive(key) {
                    return this.selectedPeriod === key;
                },
                get periodMeta() {
                    return this.periods.find(period => period.key === this.selectedPeriod) || null;
                },
                get activePeriodLabel() {
                    return this.periodMeta ? this.periodMeta.label : '';
                },
                get dayCount() {
                    return this.periodMeta ? Number(this.periodMeta.days) : 0;
                },
                get endDate() {
                    if (! this.startDate || ! this.dayCount) {
                        return '';
                    }
                    const start = new Date(this.startDate);
                    if (Number.isNaN(start.getTime())) {
                        return '';
                    }
                    const end = new Date(start);
                    end.setDate(end.getDate() + (this.dayCount - 1));
                    return end.toISOString().slice(0, 10);
                },
            }));

            Alpine.data('favoriteToggle', (config) => ({
                favorited: Boolean(config.initial),
                count: Number(config.count ?? 0),
                toggleUrl: config.toggleUrl,
                token: config.token,
                processing: false,
                error: '',
                async toggle() {
                    if (this.processing) {
                        return;
                    }
                    this.processing = true;
                    this.error = '';
                    try {
                        const response = await fetch(this.toggleUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.token,
                                'Accept': 'application/json',
                            },
                        });
                        if (! response.ok) {
                            throw new Error('リクエストに失敗しました');
                        }
                        const data = await response.json();
                        this.favorited = Boolean(data.favorited);
                        this.count = Number(data.favorites_count ?? this.count);
                    } catch (error) {
                        this.error = 'いいねの更新に失敗しました。時間をおいて再度お試しください。';
                        console.error(error);
                    } finally {
                        this.processing = false;
                    }
                },
            }));
        });
    </script>
</x-app-layout>
