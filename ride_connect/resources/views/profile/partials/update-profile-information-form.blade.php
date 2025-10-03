@php
    $initialState = $initialProfile ?? [];
    $initialState = array_merge([
        'nickname' => old('nickname', $initialState['nickname'] ?? ''),
        'prefecture_id' => (string) old('prefecture_id', $initialState['prefecture_id'] ?? ''),
        'city_id' => (string) old('city_id', $initialState['city_id'] ?? ''),
        'city' => $initialState['city'] ?? '',
        'gender' => old('gender', $initialState['gender'] ?? ''),
        'age' => (string) old('age', $initialState['age'] ?? ''),
        'license_type' => old('license_type', $initialState['license_type'] ?? []),
        'license_years_meta' => old('license_years_meta', $initialState['license_years_meta'] ?? []),
        'driving_frequency_car' => old('driving_frequency_car', $initialState['driving_frequency_car'] ?? ''),
        'driving_frequency_bike' => old('driving_frequency_bike', $initialState['driving_frequency_bike'] ?? ''),
        'owned_vehicles_car' => old('owned_vehicles_car', $initialState['owned_vehicles_car'] ?? []),
        'owned_vehicles_bike' => old('owned_vehicles_bike', $initialState['owned_vehicles_bike'] ?? []),
        'height' => old('height', $initialState['height'] ?? ''),
        'weight' => old('weight', $initialState['weight'] ?? ''),
        'bio' => old('bio', $initialState['bio'] ?? ''),
    ], $initialState);

    if (! is_array($initialState['license_type'])) {
        $decoded = json_decode($initialState['license_type'], true);
        $initialState['license_type'] = json_last_error() === JSON_ERROR_NONE
            ? $decoded
            : (strlen((string) $initialState['license_type']) ? explode(',', (string) $initialState['license_type']) : []);
    }

    if (! is_array($initialState['license_years_meta'])) {
        $decoded = json_decode($initialState['license_years_meta'], true);
        $initialState['license_years_meta'] = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
    }

    foreach (['owned_vehicles_car', 'owned_vehicles_bike'] as $vehiclesKey) {
        $value = $initialState[$vehiclesKey] ?? [];

        if (! is_array($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } elseif (is_string($value) && strlen($value)) {
                $value = array_map('trim', explode(',', $value));
            } else {
                $value = [];
            }
        }

        $initialState[$vehiclesKey] = collect($value)
            ->map(fn ($item) => is_string($item) ? trim($item) : '')
            ->filter(fn ($item) => $item !== '')
            ->take(20)
            ->values()
            ->all();
    }
@endphp

<section
    x-data="profileSettingsWizard({
        prefectures: @js($prefectures),
        licenseTypes: @js($licenseTypes),
        licenseYearOptions: @js($licenseYearOptions),
        drivingFrequencyOptions: @js($drivingFrequencyOptions),
        carLicenseTypes: @js($carLicenseTypes ?? []),
        bikeLicenseTypes: @js($bikeLicenseTypes ?? []),
        genderOptions: @js(['男性', '女性', '未回答']),
        heightOptions: @js($heightOptions ?? []),
        weightOptions: @js($weightOptions ?? []),
        initial: @js($initialState)
    })"
    x-init="init()"
>
    <header>
        <h2 class="text-lg font-medium text-slate-900">プロフィール情報</h2>
        <p class="mt-1 text-sm text-slate-600">ライコネの体験に直結するプロフィール情報を最新に保ちましょう。</p>
    </header>

    @if (session('success'))
        <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-semibold">入力内容を確認してください。</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('PATCH')

        <div class="relative overflow-hidden rounded-[30px] border border-slate-200 bg-white/95 shadow-xl shadow-slate-900/5 backdrop-blur-sm">
            <div class="absolute top-0 left-0 h-1 w-full overflow-hidden rounded-full bg-slate-200/80">
                <div class="h-full bg-gradient-to-r from-emerald-400 via-emerald-500 to-sky-400 transition-all duration-500" :style="`width: ${progressPercentage}%`"></div>
            </div>

            <div class="px-6 pb-8 pt-10 space-y-8 min-h-[360px]">
                <template x-if="step === 0">
                    <div>
                        <h3 class="text-xl font-semibold">ニックネームを設定</h3>
                        <p class="mt-2 text-sm text-slate-500">30文字以内で入力してください。検索結果やレビューに表示されます。</p>
                        <input type="text" name="nickname" x-model="form.nickname" maxlength="30" required
                               class="mt-6 rc-input" />
                    </div>
                </template>

                <template x-if="step === 1">
                    <div class="space-y-4">
                        <h3 class="text-xl font-semibold">性別を選択</h3>
                        <p class="text-sm text-slate-500">公開プロフィールに表示される性別を選択してください。</p>
                        <div>
                            <label class="text-sm text-slate-500">性別</label>
                            <select name="gender" x-model="form.gender" required class="mt-2 rc-input">
                                <option value="">選択してください</option>
                                <template x-for="option in genderOptions" :key="`settings-gender-${option}`">
                                    <option :value="option" x-text="option"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </template>

                <template x-if="step === 2">
                    <div class="space-y-4">
                        <h3 class="text-xl font-semibold">年齢を選択</h3>
                        <p class="text-sm text-slate-500">18〜100歳の範囲で選択してください。</p>
                        <div>
                            <label class="text-sm text-slate-500">年齢</label>
                            <select name="age" x-model="form.age" required class="mt-2 rc-input">
                                <option value="">選択してください</option>
                                <template x-for="option in ageOptions" :key="`settings-age-${option}`">
                                    <option :value="option" x-text="option"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </template>

                <template x-if="step === 3">
                    <div class="space-y-4">
                        <h3 class="text-xl font-semibold">身長・体重を入力</h3>
                        <p class="text-sm text-slate-500">目安で構いません。該当する選択肢を選んでください。</p>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-sm text-slate-500">身長</label>
                                <select name="height" x-model="form.height" class="mt-2 rc-input">
                                    <option value="" disabled x-bind:selected="form.height === ''">選択してください</option>
                                    <template x-for="option in heightOptions" :key="`settings-height-${option.value}`">
                                        <option :value="option.value" x-text="option.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm text-slate-500">体重</label>
                                <select name="weight" x-model="form.weight" class="mt-2 rc-input">
                                    <option value="" disabled x-bind:selected="form.weight === ''">選択してください</option>
                                    <template x-for="option in weightOptions" :key="`settings-weight-${option.value}`">
                                        <option :value="option.value" x-text="option.label"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="step === 4">
                    <div class="space-y-4">
                        <h3 class="text-xl font-semibold">受け渡し地域を選択</h3>
                        <p class="text-sm text-slate-500">都道府県と市区町村を指定すると、マッチング時の検索精度が向上します。</p>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-sm text-slate-500">都道府県</label>
                                <select name="prefecture_id" x-model="form.prefecture_id" @change="loadCities()" required
                                        class="mt-2 rc-input">
                                    <option value="">選択してください</option>
                                    <template x-for="prefecture in prefectures" :key="prefecture.id">
                                        <option :value="String(prefecture.id)" x-text="prefecture.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm text-slate-500">市区町村</label>
                                <select name="city_id" x-model="form.city_id"
                                        :disabled="!form.prefecture_id || loadingCities || cities.length === 0"
                                        class="mt-2 rc-input disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
                                    <option value="">選択してください</option>
                                    <template x-for="city in cities" :key="city.id">
                                        <option :value="String(city.id)" x-text="city.name"></option>
                                    </template>
                                </select>
                                <template x-if="loadingCities">
                                    <p class="mt-2 text-xs text-slate-400">市区町村を読み込み中です…</p>
                                </template>
                                <template x-if="form.prefecture_id && !loadingCities && cities.length === 0">
                                    <p class="mt-2 text-xs text-slate-400">市区町村が見つかりません。別の都道府県をお試しください。</p>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="step === 5">
                    <div class="space-y-4">
                        <h3 class="text-xl font-semibold">所持免許を選択</h3>
                        <p class="text-sm text-slate-500">複数選択可能です。「免許なし」を選ぶと他の免許は選択できません。</p>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <template x-for="license in licenseTypes" :key="license">
                                <label class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white/95 px-4 py-2 text-sm transition hover:border-emerald-300 hover:bg-emerald-50/60">
                                    <input type="checkbox" :value="license" name="license_type[]" x-model="form.license_type" @change="handleLicenseChange(license)"
                                           class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                                    <span x-text="license"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="step === 6">
                    <div class="space-y-4">
                        <h3 class="text-xl font-semibold">免許取得年数を選択</h3>
                        <p class="text-sm text-slate-500">免許1つずつ取得年数を入力してください。免許なしの場合は自動でスキップします。</p>
                        <template x-if="filteredLicenseYears().length">
                            <div class="grid gap-3 md:grid-cols-2">
                                <template x-for="license in filteredLicenseYears()" :key="`years-${license}`">
                                    <div class="rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 space-y-2">
                                        <p class="text-sm font-semibold text-slate-700" x-text="license"></p>
                                        <select :name="`license_years_meta[${license}]`" x-model="form.license_years_meta[license]"
                                                class="rc-input text-sm">
                                            <option value="">選択してください</option>
                                            <template x-for="option in licenseYearOptions" :key="`option-${license}-${option}`">
                                                <option :value="option" x-text="option"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!filteredLicenseYears().length">
                            <p class="rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-600">免許なしを選択しています。取得年数の入力は不要です。</p>
                        </template>
                    </div>
                </template>

                <template x-if="step === 7">
                    <div class="space-y-4">
                        <h3 class="text-xl font-semibold">運転頻度を教えてください</h3>
                        <p class="text-sm text-slate-500">所持免許に応じて必要な項目のみ表示しています。</p>
                        <div class="space-y-4 md:grid md:gap-4" :class="showCarFrequency && showBikeFrequency ? 'md:grid-cols-2' : ''">
                            <template x-if="showCarFrequency">
                                <div>
                                    <label class="text-sm text-slate-500">車</label>
                                    <select name="driving_frequency_car" x-model="form.driving_frequency_car" :disabled="drivingLocked"
                                            class="mt-2 rc-input disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
                                        <option value="">選択してください</option>
                                        <template x-for="option in drivingFrequencyOptions" :key="`car-${option}`">
                                            <option :value="option" x-text="option"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            <template x-if="showBikeFrequency">
                                <div>
                                    <label class="text-sm text-slate-500">バイク</label>
                                    <select name="driving_frequency_bike" x-model="form.driving_frequency_bike" :disabled="drivingLocked"
                                            class="mt-2 rc-input disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
                                        <option value="">選択してください</option>
                                        <template x-for="option in drivingFrequencyOptions" :key="`bike-${option}`">
                                            <option :value="option" x-text="option"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            <template x-if="!showCarFrequency && !showBikeFrequency && !drivingLocked">
                                <p class="text-xs text-slate-400">免許を選択すると必要な項目が表示されます。</p>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="step === 8">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-xl font-semibold">所有している車・バイク</h3>
                            <p class="mt-2 text-sm text-slate-500">車とバイクを分けて入力できます。各カテゴリ最大20件まで追加可能です。（各100文字以内）</p>
                        </div>

                        <div class="space-y-5">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-semibold text-slate-600">車</h4>
                                    <button type="button" class="rc-button-secondary px-3 py-2 text-xs"
                                            :disabled="form.owned_vehicles_car.length >= 20"
                                            @click="addOwnedVehicle('car')">
                                        追加
                                    </button>
                                </div>
                                <template x-if="!form.owned_vehicles_car.length">
                                    <p class="text-xs text-slate-400">所有している車があれば「追加」から登録してください。</p>
                                </template>
                                <div class="space-y-3">
                                    <template x-for="(vehicle, index) in form.owned_vehicles_car" :key="`car-${index}`">
                                        <div class="flex items-start gap-3">
                                            <input type="text" name="owned_vehicles_car[]" x-model="form.owned_vehicles_car[index]" maxlength="100"
                                                   class="rc-input flex-1"
                                                   placeholder="例）トヨタ プリウス" />
                                            <button type="button" class="text-xs text-slate-400 hover:text-rose-500"
                                                    @click="removeOwnedVehicle('car', index)">
                                                削除
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <template x-if="form.owned_vehicles_car.length >= 20">
                                    <p class="text-xs text-emerald-500">車は20件まで登録できます。</p>
                                </template>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-semibold text-slate-600">バイク</h4>
                                    <button type="button" class="rc-button-secondary px-3 py-2 text-xs"
                                            :disabled="form.owned_vehicles_bike.length >= 20"
                                            @click="addOwnedVehicle('bike')">
                                        追加
                                    </button>
                                </div>
                                <template x-if="!form.owned_vehicles_bike.length">
                                    <p class="text-xs text-slate-400">所有しているバイクがあれば「追加」から登録してください。</p>
                                </template>
                                <div class="space-y-3">
                                    <template x-for="(vehicle, index) in form.owned_vehicles_bike" :key="`bike-${index}`">
                                        <div class="flex items-start gap-3">
                                            <input type="text" name="owned_vehicles_bike[]" x-model="form.owned_vehicles_bike[index]" maxlength="100"
                                                   class="rc-input flex-1"
                                                   placeholder="例）ホンダ スーパーカブ" />
                                            <button type="button" class="text-xs text-slate-400 hover:text-rose-500"
                                                    @click="removeOwnedVehicle('bike', index)">
                                                削除
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <template x-if="form.owned_vehicles_bike.length >= 20">
                                    <p class="text-xs text-emerald-500">バイクは20件まで登録できます。</p>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="step === 9">
                    <div class="space-y-4">
                        <h3 class="text-xl font-semibold">自己紹介文を作成</h3>
                        <p class="text-sm text-slate-500">利用者へのメッセージや貸し出しのポリシーなどがあれば記載してください。（500文字以内）</p>
                        <textarea name="bio" rows="5" maxlength="500" x-model="form.bio"
                                  class="rc-input min-h-[140px] resize-y"
                                  placeholder="例）安全運転を心掛けています。お気軽にご相談ください。"></textarea>
                    </div>
                </template>
                <template x-if="step === 10">
                    <div class="space-y-6">
                        <h3 class="text-xl font-semibold">入力内容を確認</h3>
                        <div class="rounded-2xl border border-slate-200 bg-white/80 px-4 py-4 space-y-3 text-sm text-slate-600">
                            <div class="flex justify-between"><span class="text-slate-500">ニックネーム</span><span x-text="form.nickname || '-'" class="font-semibold"></span></div>
                            <div class="flex justify-between"><span class="text-slate-500">性別</span><span class="font-semibold" x-text="form.gender || '-'" ></span></div>
                            <div class="flex justify-between"><span class="text-slate-500">年齢</span><span class="font-semibold" x-text="form.age ? `${form.age}歳` : '-'" ></span></div>
                            <div class="flex justify-between"><span class="text-slate-500">身長 / 体重</span><span class="font-semibold" x-text="formattedBodyStats"></span></div>
                            <div class="flex justify-between"><span class="text-slate-500">地域</span><span x-text="prefectureName || '-'" class="font-semibold"></span></div>
                            <div class="flex justify-between"><span class="text-slate-500">市区町村</span><span x-text="cityName || '-'" class="font-semibold"></span></div>
                            <div>
                                <p class="text-slate-500">所持免許</p>
                                <p class="mt-1 font-semibold" x-text="form.license_type.length ? form.license_type.join(' / ') : '-'" ></p>
                            </div>
                            <template x-if="filteredLicenseYears().length">
                                <div>
                                    <p class="text-slate-500">免許取得年数</p>
                                    <ul class="mt-1 space-y-1">
                                        <template x-for="license in filteredLicenseYears()" :key="`review-${license}`">
                                            <li class="font-semibold" x-text="`${license}: ${form.license_years_meta[license] || '-'}`"></li>
                                        </template>
                                    </ul>
                                </div>
                            </template>
                            <template x-if="showCarFrequency || form.driving_frequency_car">
                                <div class="flex justify-between"><span class="text-slate-500">車の運転頻度</span><span class="font-semibold" x-text="form.driving_frequency_car || '-'" ></span></div>
                            </template>
                            <template x-if="showBikeFrequency || form.driving_frequency_bike">
                                <div class="flex justify-between"><span class="text-slate-500">バイクの運転頻度</span><span class="font-semibold" x-text="form.driving_frequency_bike || '-'" ></span></div>
                            </template>
                            <div>
                                <p class="text-slate-500">所有車両（車）</p>
                                <template x-if="cleanedOwnedVehiclesCar.length">
                                    <ul class="mt-1 space-y-1">
                                        <template x-for="(vehicle, index) in cleanedOwnedVehiclesCar" :key="`review-owned-car-${index}`">
                                            <li class="font-semibold" x-text="vehicle"></li>
                                        </template>
                                    </ul>
                                </template>
                                <template x-if="!cleanedOwnedVehiclesCar.length">
                                    <p class="mt-1 font-semibold">-</p>
                                </template>
                            </div>
                            <div>
                                <p class="text-slate-500">所有車両（バイク）</p>
                                <template x-if="cleanedOwnedVehiclesBike.length">
                                    <ul class="mt-1 space-y-1">
                                        <template x-for="(vehicle, index) in cleanedOwnedVehiclesBike" :key="`review-owned-bike-${index}`">
                                            <li class="font-semibold" x-text="vehicle"></li>
                                        </template>
                                    </ul>
                                </template>
                                <template x-if="!cleanedOwnedVehiclesBike.length">
                                    <p class="mt-1 font-semibold">-</p>
                                </template>
                            </div>
                            <div>
                                <p class="text-slate-500">自己紹介</p>
                                <p class="mt-1 whitespace-pre-line font-semibold" x-text="form.bio || '-'" ></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200/70 bg-white/70 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-rose-500" x-text="stepError"></div>
                <div class="flex flex-wrap gap-3">
                    <button type="button" class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-600"
                            x-show="step > 0"
                            @click="prev">
                        戻る
                    </button>
                    <button type="button" class="rc-button-secondary"
                            x-show="step < maxStep"
                            @click="next">
                        次へ
                    </button>
                    <button type="submit" class="rc-button-primary"
                            x-show="step === maxStep">
                        保存する
                    </button>
                </div>
            </div>
            <template x-if="step === maxStep">
                <div class="hidden" aria-hidden="true">
                    <input type="hidden" name="nickname" :value="form.nickname">
                    <input type="hidden" name="gender" :value="form.gender">
                    <input type="hidden" name="age" :value="form.age">
                    <input type="hidden" name="prefecture_id" :value="form.prefecture_id">
                    <input type="hidden" name="city_id" :value="form.city_id">
                    <template x-for="license in form.license_type" :key="`hidden-license-${license}`">
                        <input type="hidden" name="license_type[]" :value="license">
                    </template>
                    <template x-for="([license, value], index) in licenseYearEntries" :key="`hidden-license-years-${license}-${index}`">
                        <input type="hidden" :name="`license_years_meta[${license}]`" :value="value">
                    </template>
                    <input type="hidden" name="license_years_meta_json" :value="licenseYearsJson">
                    <input type="hidden" name="driving_frequency_car" :value="showCarFrequency || drivingLocked ? form.driving_frequency_car : ''">
                    <input type="hidden" name="driving_frequency_bike" :value="showBikeFrequency || drivingLocked ? form.driving_frequency_bike : ''">
                    <template x-for="(vehicle, index) in cleanedOwnedVehiclesCar" :key="`hidden-owned-car-${index}`">
                        <input type="hidden" name="owned_vehicles_car[]" :value="vehicle">
                    </template>
                    <template x-for="(vehicle, index) in cleanedOwnedVehiclesBike" :key="`hidden-owned-bike-${index}`">
                        <input type="hidden" name="owned_vehicles_bike[]" :value="vehicle">
                    </template>
                    <input type="hidden" name="height" :value="form.height">
                    <input type="hidden" name="weight" :value="form.weight">
                    <input type="hidden" name="bio" :value="form.bio">
                </div>
            </template>
        </div>
    </form>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('profileSettingsWizard', ({ prefectures, licenseTypes, licenseYearOptions, drivingFrequencyOptions, carLicenseTypes, bikeLicenseTypes, genderOptions, heightOptions, weightOptions, initial }) => ({
                step: 0,
                maxStep: 10,
                stepError: '',
                prefectures,
                licenseTypes,
                licenseYearOptions,
                drivingFrequencyOptions,
                carLicenseTypes,
                bikeLicenseTypes,
                genderOptions,
                heightOptions,
                weightOptions,
                ageOptions: Array.from({ length: 83 }, (_, index) => String(index + 18)),
                cities: [],
                loadingCities: false,
                drivingLocked: false,
                initialCityName: initial.city || '',
                form: {
                    nickname: initial.nickname || '',
                    prefecture_id: initial.prefecture_id ? String(initial.prefecture_id) : '',
                    city_id: initial.city_id ? String(initial.city_id) : '',
                    gender: initial.gender || '',
                    age: initial.age ? String(initial.age) : '',
                    license_type: Array.isArray(initial.license_type) ? [...initial.license_type] : [],
                    license_years_meta: initial.license_years_meta || {},
                    driving_frequency_car: initial.driving_frequency_car || '',
                    driving_frequency_bike: initial.driving_frequency_bike || '',
                    owned_vehicles_car: Array.isArray(initial.owned_vehicles_car) ? [...initial.owned_vehicles_car] : [],
                    owned_vehicles_bike: Array.isArray(initial.owned_vehicles_bike) ? [...initial.owned_vehicles_bike] : [],
                    height: initial.height !== null && initial.height !== undefined
                        ? String(initial.height)
                        : '',
                    weight: initial.weight !== null && initial.weight !== undefined
                        ? String(initial.weight)
                        : '',
                    bio: initial.bio || '',
                },
                get cleanedOwnedVehiclesCar() {
                    return this.sanitizeOwnedVehicles('car');
                },
                get cleanedOwnedVehiclesBike() {
                    return this.sanitizeOwnedVehicles('bike');
                },
                get sanitizedLicenseYears() {
                    return (this.form.license_type || []).reduce((acc, license) => {
                        const raw = this.form.license_years_meta?.[license];
                        let value = Array.isArray(raw) ? raw[0] : raw;

                        if (value === null || value === undefined) {
                            return acc;
                        }

                        value = String(value).trim();

                        if (value !== '') {
                            acc[license] = value;
                        }

                        return acc;
                    }, {});
                },
                get licenseYearEntries() {
                    return Object.entries(this.sanitizedLicenseYears);
                },
                get licenseYearsJson() {
                    return JSON.stringify(this.sanitizedLicenseYears);
                },
                get showCarFrequency() {
                    return ! this.drivingLocked && this.requiresCarFrequency();
                },
                get showBikeFrequency() {
                    return ! this.drivingLocked && this.requiresBikeFrequency();
                },
                get progressPercentage() {
                    return Math.min((this.step / this.maxStep) * 100, 100);
                },
                get prefectureName() {
                    const pref = this.prefectures.find((p) => String(p.id) === String(this.form.prefecture_id));
                    return pref ? pref.name : '';
                },
                get cityName() {
                    const city = this.cities.find((c) => String(c.id) === String(this.form.city_id));
                    return city ? city.name : '';
                },
                get formattedBodyStats() {
                    const height = this.form.height ? this.lookupHeightLabel(this.form.height) : '-';
                    const weight = this.form.weight ? this.lookupWeightLabel(this.form.weight) : '-';
                    return `${height} / ${weight}`;
                },
                init() {
                    this.syncDrivingLock();
                    if (! Array.isArray(this.form.license_type)) {
                        this.form.license_type = [];
                    }
                    if (this.form.age && ! Number.isNaN(Number(this.form.age))) {
                        this.form.age = String(this.form.age);
                    }
                    if (this.form.prefecture_id) {
                        this.loadCities(true);
                    }
                    window.profileSettingsWizard = this;
                },
                async loadCities(preserveSelection = false) {
                    this.cities = [];
                    this.loadingCities = true;
                    this.form.city_id = preserveSelection ? this.form.city_id : '';

                    if (! this.form.prefecture_id) {
                        this.loadingCities = false;
                        return;
                    }

                    try {
                        const response = await fetch(`/api/prefectures/${this.form.prefecture_id}/cities`);
                        if (response.ok) {
                            this.cities = await response.json();
                            if (preserveSelection && this.initialCityName) {
                                const matched = this.cities.find((city) => city.name === this.initialCityName);
                                if (matched) {
                                    this.form.city_id = String(matched.id);
                                }
                            }
                        }
                    } catch (error) {
                        console.error('Failed to load cities', error);
                    } finally {
                        this.loadingCities = false;
                    }
                },
                handleLicenseChange(license) {
                    if (license === '免許なし' && this.form.license_type.includes('免許なし')) {
                        this.form.license_type = ['免許なし'];
                    } else {
                        this.form.license_type = this.form.license_type.filter((item) => item !== '免許なし');
                    }
                    this.removeUnusedLicenseYears();
                    this.syncDrivingLock();
                },
                filteredLicenseYears() {
                    return this.form.license_type.filter((license) => license !== '免許なし');
                },
                removeUnusedLicenseYears() {
                    Object.keys(this.form.license_years_meta || {}).forEach((key) => {
                        if (! this.form.license_type.includes(key)) {
                            delete this.form.license_years_meta[key];
                        }
                    });
                },
                syncDrivingLock() {
                    const hasNoLicense = this.form.license_type.includes('免許なし');
                    this.drivingLocked = hasNoLicense;
                    if (hasNoLicense) {
                        this.form.license_years_meta = {};
                        this.form.driving_frequency_car = '免許なし';
                        this.form.driving_frequency_bike = '免許なし';
                    } else {
                        if (this.form.driving_frequency_car === '免許なし') {
                            this.form.driving_frequency_car = '';
                        }
                        if (this.form.driving_frequency_bike === '免許なし') {
                            this.form.driving_frequency_bike = '';
                        }
                    }
                    this.syncDrivingVisibility();
                },
                syncDrivingVisibility() {
                    if (this.drivingLocked) {
                        return;
                    }

                    if (! this.requiresCarFrequency()) {
                        this.form.driving_frequency_car = '';
                    }

                    if (! this.requiresBikeFrequency()) {
                        this.form.driving_frequency_bike = '';
                    }
                },
                requiresCarFrequency() {
                    return this.form.license_type.some((license) => this.carLicenseTypes.includes(license));
                },
                requiresBikeFrequency() {
                    return this.form.license_type.some((license) => this.bikeLicenseTypes.includes(license));
                },
                lookupHeightLabel(value) {
                    const numeric = Number(value);
                    const option = this.heightOptions.find((item) => Number(item.value) === numeric);
                    return option ? option.label : `${numeric}cm`;
                },
                lookupWeightLabel(value) {
                    const numeric = Number(value);
                    const option = this.weightOptions.find((item) => Number(item.value) === numeric);
                    return option ? option.label : `${numeric}kg`;
                },
                sanitizeOwnedVehicles(type) {
                    const key = type === 'car' ? 'owned_vehicles_car' : 'owned_vehicles_bike';
                    const source = Array.isArray(this.form[key]) ? this.form[key] : [];

                    return source
                        .map((value) => (value ?? '').toString().trim())
                        .filter((value) => value !== '')
                        .slice(0, 20);
                },
                addOwnedVehicle(type) {
                    const key = type === 'car' ? 'owned_vehicles_car' : 'owned_vehicles_bike';
                    if (! Array.isArray(this.form[key])) {
                        this.form[key] = [];
                    }

                    if (this.form[key].length >= 20) {
                        return;
                    }

                    this.form[key] = [...this.form[key], ''];
                },
                removeOwnedVehicle(type, index) {
                    const key = type === 'car' ? 'owned_vehicles_car' : 'owned_vehicles_bike';
                    if (! Array.isArray(this.form[key])) {
                        return;
                    }

                    this.form[key].splice(index, 1);
                },
                next() {
                    this.stepError = '';
                    switch (this.step) {
                        case 0:
                            if (! this.form.nickname.trim()) {
                                this.stepError = 'ニックネームを入力してください。';
                                return;
                            }
                            break;
                        case 1:
                            if (! this.form.gender) {
                                this.stepError = '性別を選択してください。';
                                return;
                            }
                            break;
                        case 2:
                            if (! this.form.age) {
                                this.stepError = '年齢を選択してください。';
                                return;
                            }
                            break;
                        case 4:
                            if (! this.form.prefecture_id) {
                                this.stepError = '都道府県を選択してください。';
                                return;
                            }
                            if (! this.form.city_id) {
                                this.stepError = '市区町村を選択してください。';
                                return;
                            }
                            break;
                        case 5:
                            if (! this.form.license_type.length) {
                                this.stepError = '所持免許を少なくとも1つ選択してください。';
                                return;
                            }
                            break;
                        case 6:
                            if (this.filteredLicenseYears().length) {
                                const missing = this.filteredLicenseYears().find((license) => ! this.form.license_years_meta[license]);
                                if (missing) {
                                    this.stepError = `${missing} の取得年数を選択してください。`;
                                    return;
                                }
                            }
                            break;
                        case 7:
                            if (! this.drivingLocked) {
                                if (this.requiresCarFrequency() && ! this.form.driving_frequency_car) {
                                    this.stepError = '車の運転頻度を選択してください。';
                                    return;
                                }
                                if (this.requiresBikeFrequency() && ! this.form.driving_frequency_bike) {
                                    this.stepError = 'バイクの運転頻度を選択してください。';
                                    return;
                                }
                            }
                            break;
                    }
                    this.step = Math.min(this.step + 1, this.maxStep);
                },
                prev() {
                    this.stepError = '';
                    this.step = Math.max(this.step - 1, 0);
                },
                jumpToStep(targetStep) {
                    const stepNumber = Number(targetStep);
                    if (Number.isNaN(stepNumber)) {
                        return;
                    }
                    this.stepError = '';
                    this.step = Math.min(Math.max(stepNumber, 0), this.maxStep);
                    this.$nextTick(() => this.scrollIntoView());
                },
                scrollIntoView() {
                    const target = this.$root || this.$el;
                    if (target && typeof target.scrollIntoView === 'function') {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                },
            }));
        });
    </script>
</section>
