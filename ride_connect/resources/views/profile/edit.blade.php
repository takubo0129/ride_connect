<x-app-layout>
    <x-slot name="header">
        <div class="space-y-2">
            <div class="rc-glow-tag">PROFILE</div>
            <h1 class="text-3xl font-semibold text-slate-900 leading-tight">{{ __('設定') }}</h1>
            <p class="text-sm text-slate-500">取引前にプロフィールとアカウントを整えておきましょう。</p>
        </div>
    </x-slot>

    <div class="rc-shell">
        <div class="grid gap-8 xl:grid-cols-[1.55fr_1fr]">
            <div class="space-y-8">
                <div class="rc-card p-8 sm:p-10">
                    <div class="space-y-6">
                        @include('profile.partials.update-profile-information-form', [
                            'prefectures' => $prefectures,
                            'licenseTypes' => $licenseTypes,
                            'licenseYearOptions' => $licenseYearOptions,
                            'drivingFrequencyOptions' => $drivingFrequencyOptions,
                            'carLicenseTypes' => $carLicenseTypes,
                            'bikeLicenseTypes' => $bikeLicenseTypes,
                            'initialProfile' => $initialProfile,
                        ])
                    </div>
                </div>

                <div class="rc-card p-8 sm:p-10">
                    <div class="space-y-6">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="rc-card p-8 sm:p-10">
                    <div class="space-y-6">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>

            @if ($profile)
                @php
                    $licenseYearsLines = collect($profile->license_years_meta ?? [])->map(fn ($years, $license) => "{$license}: {$years}");
                    $bodyStats = ($profile->height || $profile->weight)
                        ? trim(($profile->height ? $profile->height . 'cm' : '-') . ' / ' . ($profile->weight ? $profile->weight . 'kg' : '-'))
                        : '未設定';

                    $summaryOwnedVehicleCars = [];
                    $summaryOwnedVehicleBikes = [];
                    $rawOwnedVehicles = $profile->owned_vehicles;

                    if (is_array($rawOwnedVehicles) && $rawOwnedVehicles !== []) {
                        if (array_is_list($rawOwnedVehicles)) {
                            foreach ($rawOwnedVehicles as $vehicle) {
                                if (! is_array($vehicle)) {
                                    continue;
                                }

                                $type = $vehicle['type'] ?? null;
                                if (! in_array($type, ['car', 'bike'], true)) {
                                    continue;
                                }

                                $maker = trim((string) ($vehicle['maker'] ?? ''));
                                $makerCustom = trim((string) ($vehicle['maker_custom'] ?? ''));
                                $model = trim((string) ($vehicle['model'] ?? ''));

                                $makerLabel = $maker === 'その他'
                                    ? ($makerCustom !== '' ? $makerCustom : 'その他')
                                    : $maker;

                                $label = trim($makerLabel . ' ' . $model);

                                if ($label === '') {
                                    continue;
                                }

                                if ($type === 'car') {
                                    $summaryOwnedVehicleCars[] = $label;
                                } else {
                                    $summaryOwnedVehicleBikes[] = $label;
                                }
                            }
                        } else {
                            $summaryOwnedVehicleCars = collect($rawOwnedVehicles['cars'] ?? [])
                                ->map(fn ($value) => trim((string) $value))
                                ->filter(fn ($value) => $value !== '')
                                ->values()
                                ->take(20)
                                ->all();

                            $summaryOwnedVehicleBikes = collect($rawOwnedVehicles['bikes'] ?? [])
                                ->map(fn ($value) => trim((string) $value))
                                ->filter(fn ($value) => $value !== '')
                                ->values()
                                ->take(20)
                                ->all();
                        }
                    }

                    if ($summaryOwnedVehicleCars === [] && $summaryOwnedVehicleBikes === [] && filled($profile->owned_vehicle)) {
                        $fallback = collect(explode('/', (string) $profile->owned_vehicle))
                            ->map(fn ($value) => trim($value))
                            ->filter(fn ($value) => $value !== '')
                            ->take(20)
                            ->values()
                            ->all();

                        if ($fallback !== []) {
                            $summaryOwnedVehicleCars = $fallback;
                        }
                    }
                @endphp
                <aside class="space-y-6">
                    <div class="rc-card p-6 sm:p-8 lg:sticky lg:top-28" x-data="{ edit(step) { window.profileSettingsWizard?.jumpToStep(step); } }">
                        <div class="space-y-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">現在のプロフィール</h2>
                                <p class="mt-1 text-sm text-slate-500">保存済みの情報を確認し、必要なステップに瞬時に移動できます。</p>
                            </div>

                            <div class="space-y-3">
                                <div class="profile-summary-tile">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">ニックネーム</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $profile->nickname ?? '未設定' }}</p>
                                    </div>
                                    <button type="button" @click="edit(0)">編集</button>
                                </div>

                                <div class="profile-summary-tile">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">地域</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $profile->region ?? '未設定' }}</p>
                                        <p class="text-sm font-semibold text-slate-900">{{ $profile->city ?? '未設定' }}</p>
                                    </div>
                                    <button type="button" @click="edit(1)">編集</button>
                                </div>

                                <div class="profile-summary-tile">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">所持免許</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ filled($profile->license_type) ? implode(' / ', (array) $profile->license_type) : '未設定' }}</p>
                                    </div>
                                    <button type="button" @click="edit(2)">編集</button>
                                </div>

                                <div class="profile-summary-tile">
                                    <div class="flex-1">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">免許取得年数</p>
                                        <div class="mt-1 space-y-1 text-sm font-semibold text-slate-900">
                                            @forelse ($licenseYearsLines as $line)
                                                <span class="block">{{ $line }}</span>
                                            @empty
                                                <span class="block">未設定</span>
                                            @endforelse
                                        </div>
                                    </div>
                                    <button type="button" @click="edit(3)">編集</button>
                                </div>

                                <div class="profile-summary-tile">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">運転頻度</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">車: {{ $profile->driving_frequency_car ?? '未設定' }}</p>
                                        <p class="text-sm font-semibold text-slate-900">バイク: {{ $profile->driving_frequency_bike ?? '未設定' }}</p>
                                    </div>
                                    <button type="button" @click="edit(4)">編集</button>
                                </div>

                                <div class="profile-summary-tile">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">所有車両</p>
                                        <div class="mt-1 space-y-1 text-sm font-semibold text-slate-900">
                                            <p>車: {{ $summaryOwnedVehicleCars ? implode(' / ', $summaryOwnedVehicleCars) : '未設定' }}</p>
                                            <p>バイク: {{ $summaryOwnedVehicleBikes ? implode(' / ', $summaryOwnedVehicleBikes) : '未設定' }}</p>
                                        </div>
                                    </div>
                                    <button type="button" @click="edit(5)">編集</button>
                                </div>

                                <div class="profile-summary-tile">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">性別 / 年齢</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">性別: {{ $profile->gender ?? '未設定' }}</p>
                                        <p class="text-sm font-semibold text-slate-900">年齢: {{ $profile->age ? $profile->age . '歳' : '未設定' }}</p>
                                    </div>
                                    <button type="button" @click="edit(6)">編集</button>
                                </div>

                                <div class="profile-summary-tile">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">身長 / 体重</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $bodyStats }}</p>
                                    </div>
                                    <button type="button" @click="edit(7)">編集</button>
                                </div>

                                <div class="profile-summary-tile">
                                    <div class="flex-1">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">自己紹介</p>
                                        <p class="mt-1 whitespace-pre-line text-sm font-semibold text-slate-900">{{ $profile->bio ?? '未設定' }}</p>
                                    </div>
                                    <button type="button" @click="edit(8)">編集</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            @endif
        </div>
    </div>

    @include('partials.bottom-navigation')
</x-app-layout>
