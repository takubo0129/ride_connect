@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <div class="rc-glow-tag">FAVORITES</div>
            <h1 class="text-3xl font-semibold text-slate-900 leading-tight">いいね一覧</h1>
            <p class="text-sm text-slate-500">「詳細」ページの <span class="text-base">❤️</span> ボタンから保存した車両がここに表示されます。比較検討や再度の予約申請にご活用ください。</p>
        </div>
    </x-slot>

    <div class="rc-shell">
        @if ($favorites->isEmpty())
            <section class="rc-card p-10 text-center text-sm text-slate-500">
                まだ「いいね」した車両がありません。気になる車両ページでハートアイコンをタップすると、ここに保存されます。
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('vehicles.index') }}" class="rc-button-primary">
                        🔍 車両を探す
                    </a>
                    <a href="{{ route('home') }}" class="rc-button-secondary">
                        🏠 ホームへ戻る
                    </a>
                </div>
            </section>
        @else
            <section class="space-y-4">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($favorites as $favorite)
                        @php
                            $vehicle = $favorite->vehicle;
                            $thumb = is_array($vehicle->images) && count($vehicle->images) ? Storage::url($vehicle->images[0]) : null;
                            $location = trim(($vehicle->prefecture->name ?? '') . ' ' . ($vehicle->city->name ?? ''));
                        @endphp
                        <a href="{{ route('vehicles.show', $vehicle) }}" class="group flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white/95 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                            <div class="relative h-44 bg-slate-100">
                                @if ($thumb)
                                    <img src="{{ $thumb }}" alt="{{ $vehicle->model }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-slate-500">写真は準備中です</div>
                                @endif
                                <span class="absolute left-4 top-4 inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-emerald-700 shadow-sm">
                                    {{ $vehicle->type === 'car' ? '車' : 'バイク' }}
                                </span>
                            </div>
                            <div class="flex flex-1 flex-col gap-3 p-6">
                                <div class="space-y-1">
                                    <p class="text-xs uppercase tracking-[0.26em] text-slate-400">{{ $location ?: '地域未設定' }}</p>
                                    <h2 class="text-lg font-semibold text-slate-900 transition group-hover:text-emerald-600">
                                        {{ $vehicle->display_maker }} {{ $vehicle->model }}
                                    </h2>
                                </div>
                                <div class="mt-auto flex items-center justify-between text-sm text-slate-500">
                                    <span>{{ $vehicle->year ? $vehicle->year . '年式' : '年式不明' }}</span>
                                    <span class="text-base font-semibold text-emerald-600">¥{{ number_format($vehicle->price_per_day) }} / 日</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    @include('partials.bottom-navigation')
</x-app-layout>
