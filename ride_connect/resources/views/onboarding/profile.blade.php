<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>プロフィール登録 | ライコネ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-slate-900">
<div class="min-h-screen flex flex-col items-center justify-center px-6 py-12 bg-slate-50">
    <div
        x-data="profileWizard({
            prefectures: {{ $prefectures->toJson() }},
            licenseTypes: {{ json_encode($licenseTypes, JSON_UNESCAPED_UNICODE) }},
            licenseYearOptions: {{ json_encode($licenseYearOptions, JSON_UNESCAPED_UNICODE) }},
            drivingFrequencyOptions: {{ json_encode($drivingFrequencyOptions, JSON_UNESCAPED_UNICODE) }},
            carMakerGroups: {{ json_encode($carMakerGroups, JSON_UNESCAPED_UNICODE) }},
            bikeMakerGroups: {{ json_encode($bikeMakerGroups, JSON_UNESCAPED_UNICODE) }},
            carLicenseTypes: {{ json_encode($carLicenseTypes, JSON_UNESCAPED_UNICODE) }},
            bikeLicenseTypes: {{ json_encode($bikeLicenseTypes, JSON_UNESCAPED_UNICODE) }},
            profile: {{ json_encode(optional($profile)->only(['nickname','region','city','gender','age','license_type','license_years_meta','driving_frequency_car','driving_frequency_bike','owned_vehicles','height','weight','bio']), JSON_UNESCAPED_UNICODE) }},
            heightOptions: {{ json_encode($heightOptions, JSON_UNESCAPED_UNICODE) }},
            weightOptions: {{ json_encode($weightOptions, JSON_UNESCAPED_UNICODE) }},
            genderOptions: {{ json_encode(['男性','女性','未回答'], JSON_UNESCAPED_UNICODE) }}
        })"
        x-init="init()"
        class="w-full max-w-2xl">
        <form method="POST" action="{{ route('onboarding.profile.store') }}" class="bg-white border border-slate-300 rounded-3xl shadow-xl p-8 sm:p-12 relative overflow-hidden">
            @csrf

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                    <p class="font-semibold">入力内容を確認してください。</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <!-- progress indicator -->
            <div class="absolute top-0 left-0 w-full h-1 bg-slate-200">
                <div class="h-full bg-emerald-400 transition-all duration-500" :style="`width: ${progressPercentage}%`"></div>
            </div>

            <!-- Step content -->
            <div class="min-h-[320px] flex flex-col justify-center mt-6">
                <!-- Logo intro -->
                <template x-if="step === 0">
                    <div class="flex flex-col items-center text-center space-y-6">
                        <div class="h-24 w-24 rounded-full border border-emerald-400 flex items-center justify-center text-xl font-semibold">
                            ライコネ
                        </div>
                        <p class="text-slate-600">あなたとモビリティをつなぐ入り口へ</p>
                    </div>
                </template>

                <!-- Welcome message -->
                <template x-if="step === 1">
                    <div class="text-center space-y-4">
                        <h1 class="text-2xl font-semibold">ライコネにようこそ</h1>
                        <p class="text-slate-600">いくつか質問に答えて、あなただけの体験をはじめましょう。</p>
                    </div>
                </template>

                <!-- Nickname -->
                <template x-if="step === 2">
                    <div>
                        <h2 class="text-xl font-semibold mb-4">ニックネームを設定してください</h2>
                        <p class="text-sm text-slate-500 mb-6">公開時の表示名になります。（30文字以内）</p>
                        <input type="text" name="nickname" x-model="form.nickname" maxlength="30" required
                               class="w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3" />
                    </div>
                </template>

                <!-- Region -->
                <template x-if="step === 3">
                    <div>
                        <h2 class="text-xl font-semibold mb-4">地域を選択してください</h2>
                        <div class="grid gap-4">
                            <div>
                                <label class="text-sm text-slate-500">都道府県</label>
                                <select x-model="form.prefecture_id" @change="handlePrefectureChange" required
                                        class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3">
                                    <option value="" disabled selected>選択してください</option>
                                    <template x-for="prefecture in prefectures" :key="prefecture.id">
                                        <option :value="prefecture.id" x-text="prefecture.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm text-slate-500">市区町村</label>
                                <select name="city" x-model="form.city" required
                                        class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3">
                                    <option value="" disabled selected>選択してください</option>
                                    <template x-for="city in cities" :key="city.id">
                                        <option :value="city.name" x-text="city.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="region" :value="form.region">
                    </div>
                </template>

                <!-- License types -->
                <template x-if="step === 4">
                    <div>
                        <h2 class="text-xl font-semibold mb-4">所有している免許を選択してください</h2>
                        <p class="text-sm text-slate-500 mb-4">複数選択できます</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <template x-for="license in licenseTypes" :key="license">
                                <label class="flex items-center space-x-3 rounded-xl border px-4 py-3 transition"
                                       :class="form.license_type.includes(license) ? 'border-emerald-400 bg-emerald-50 shadow-sm' : 'border-slate-300 bg-slate-50 hover:border-emerald-300 hover:bg-emerald-50/50'">
                                    <input type="checkbox"
                                           :value="license"
                                           name="license_type[]"
                                           :checked="form.license_type.includes(license)"
                                           :disabled="form.license_type.includes('免許なし') && license !== '免許なし'"
                                           @change="toggleLicense($event.target.value, $event.target.checked)"
                                           class="rounded border-slate-300 text-emerald-400 focus:ring-emerald-400 disabled:opacity-40">
                                    <span x-text="license"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- License years -->
                <template x-if="step === 5">
                    <div>
                        <h2 class="text-xl font-semibold mb-4">免許の取得年数を教えてください</h2>
                        <p class="text-sm text-slate-500 mb-4">各免許ごとに選択してください</p>
                        <div class="space-y-4 max-h-72 overflow-y-auto pr-2">
                            <template x-if="form.license_type.length === 0">
                                <p class="text-sm text-slate-500">免許を選択すると項目が表示されます。</p>
                            </template>
                            <template x-for="license in form.license_type" :key="license">
                                <div class="bg-slate-50 rounded-xl border border-slate-300 px-4 py-3 space-y-2">
                                    <p class="text-sm font-medium" x-text="license"></p>
                                    <template x-if="license === '免許なし'">
                                        <p class="text-xs text-slate-500">「免許なし」を選択したため、取得年数の入力は不要です。</p>
                                    </template>
                                    <template x-if="license !== '免許なし'">
                                        <select :name="`license_years_meta[${license}]`" x-model="form.license_years_meta[license]"
                                                class="w-full rounded-lg border focus:border-emerald-400 focus:ring-0 px-3 py-2 transition"
                                                :class="drivingLocked ? 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed' : 'bg-white border-slate-300'"
                                                :disabled="drivingLocked">
                                            <option value="" disabled selected>選択してください</option>
                                            <template x-for="option in licenseYearOptions" :key="option">
                                                <option :value="option" x-text="option"></option>
                                            </template>
                                        </select>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Driving frequency -->
                <template x-if="step === 6">
                    <div>
                        <h2 class="text-xl font-semibold mb-4">車・バイクの運転頻度を教えてください</h2>
                        <div class="space-y-4 sm:grid sm:gap-4" :class="showCarFrequency && showBikeFrequency ? 'sm:grid-cols-2' : ''">
                            <template x-if="showCarFrequency">
                                <div class="bg-slate-50 rounded-xl border border-slate-300 px-4 py-3 space-y-2">
                                    <p class="text-sm text-slate-600">車</p>
                                    <select name="driving_frequency_car" x-model="form.driving_frequency_car"
                                            class="w-full rounded-lg border focus:border-emerald-400 focus:ring-0 px-3 py-2 transition"
                                            :class="drivingLocked ? 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed' : 'bg-white border-slate-300'"
                                            :disabled="drivingLocked">
                                        <option value="" disabled selected>選択してください</option>
                                        <template x-for="option in drivingFrequencyOptions" :key="'car-' + option">
                                            <option :value="option" x-text="option"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            <template x-if="showBikeFrequency">
                                <div class="bg-slate-50 rounded-xl border border-slate-300 px-4 py-3 space-y-2">
                                    <p class="text-sm text-slate-600">バイク</p>
                                    <select name="driving_frequency_bike" x-model="form.driving_frequency_bike"
                                            class="w-full rounded-lg border focus:border-emerald-400 focus:ring-0 px-3 py-2 transition"
                                            :class="drivingLocked ? 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed' : 'bg-white border-slate-300'"
                                            :disabled="drivingLocked">
                                        <option value="" disabled selected>選択してください</option>
                                        <template x-for="option in drivingFrequencyOptions" :key="'bike-' + option">
                                            <option :value="option" x-text="option"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            <template x-if="!showCarFrequency && !showBikeFrequency && !drivingLocked">
                                <p class="text-xs text-slate-500">対象の免許を選択すると必要な項目が表示されます。</p>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Owned vehicles -->
                <template x-if="step === 7">
                    <div class="space-y-6">
                        <h2 class="text-xl font-semibold">現在、所有している車・バイクはありますか？</h2>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="button"
                                    class="inline-flex items-center justify-center rounded-xl bg-emerald-400 text-slate-900 font-semibold px-6 py-3 hover:bg-emerald-300 transition"
                                    @click="startAddOwnedVehicle">
                                ＋追加する
                            </button>
                            <button type="button"
                                    class="inline-flex items-center justify-center rounded-xl border px-6 py-3 transition-colors"
                                    :class="form.owns_none ? 'border-emerald-400 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:border-emerald-500' : 'border-slate-300 text-slate-600 hover:border-emerald-400 hover:text-emerald-600 hover:bg-emerald-50/40'"
                                    @click="setOwnsNone">
                                所有していない
                            </button>
                        </div>

                        <template x-if="form.owns_none">
                            <p class="text-sm text-slate-500">所有している車・バイクはありません。</p>
                        </template>

                        <template x-if="!form.owns_none && form.owned_vehicles.length === 0">
                            <p class="text-sm text-slate-500">「＋追加する」から所有している車・バイクを登録してください。</p>
                        </template>

                        <p class="text-sm text-rose-500" x-show="ownedVehicleError" x-text="ownedVehicleError"></p>

                        <div class="space-y-4" x-show="form.owned_vehicles.length">
                            <template x-for="(vehicle, index) in form.owned_vehicles" :key="'owned-' + index">
                                <div class="border border-slate-300 bg-slate-50 rounded-xl p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <p class="font-semibold" x-text="ownedVehicleSummary(vehicle)"></p>
                                        <button type="button"
                                                class="text-xs text-slate-500 hover:text-emerald-300 transition"
                                                @click="removeOwnedVehicle(index)">
                                            削除
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div x-show="ownedVehicleFormVisible"
                             class="border border-emerald-400/40 bg-white rounded-2xl p-5 space-y-4">
                            <div class="flex gap-3">
                                <button type="button"
                                        class="flex-1 rounded-xl px-4 py-2 transition"
                                        :class="newOwnedVehicle.type === 'car' ? 'bg-emerald-400 text-slate-900 font-semibold' : 'bg-slate-50 text-slate-600'"
                                        @click="setOwnedVehicleType('car')">
                                    車
                                </button>
                                <button type="button"
                                        class="flex-1 rounded-xl px-4 py-2 transition"
                                        :class="newOwnedVehicle.type === 'bike' ? 'bg-emerald-400 text-slate-900 font-semibold' : 'bg-slate-50 text-slate-600'"
                                        @click="setOwnedVehicleType('bike')">
                                    バイク
                                </button>
                            </div>

                            <div>
                                <label class="text-sm text-slate-500">メーカー</label>
                                <select x-model="newOwnedVehicle.maker"
                                        class="mt-2 w-full rounded-xl bg-white border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3">
                                    <option value="" disabled>選択してください</option>
                                    <template x-for="group in currentMakerGroups" :key="group.label">
                                        <optgroup :label="group.label">
                                            <template x-for="maker in group.options" :key="maker">
                                                <option :value="maker" x-text="maker"></option>
                                            </template>
                                        </optgroup>
                                    </template>
                                </select>
                            </div>

                            <div x-show="newOwnedVehicle.maker === 'その他'">
                                <label class="text-sm text-slate-500">メーカー名（自由入力）</label>
                                <input type="text" x-model="newOwnedVehicle.maker_custom" maxlength="100"
                                       class="mt-2 w-full rounded-xl bg-white border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3">
                            </div>

                            <div>
                                <label class="text-sm text-slate-500">車種名</label>
                                <input type="text" x-model="newOwnedVehicle.model" maxlength="100"
                                       class="mt-2 w-full rounded-xl bg-white border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3">
                            </div>

                            <div class="flex justify-end gap-3 pt-2">
                                <button type="button"
                                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition"
                                        @click="cancelOwnedVehicleForm">
                                    キャンセル
                                </button>
                                <button type="button"
                                        class="rounded-xl bg-emerald-400 text-slate-900 font-semibold px-4 py-2 text-sm hover:bg-emerald-300 transition"
                                        @click="saveOwnedVehicle()">
                                    追加する
                                </button>
                            </div>
                        </div>

                    </div>
                </template>

                <!-- Gender & age -->
                <template x-if="step === 8">
                    <div>
                        <h2 class="text-xl font-semibold mb-4">性別と年齢を教えてください</h2>
                        <p class="text-sm text-slate-500 mb-6">公開プロフィールに表示されます。年齢は18〜100歳から選択できます。</p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-sm text-slate-500">性別</label>
                                <select name="gender" x-model="form.gender" required
                                        class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3">
                                    <option value="" disabled selected>選択してください</option>
                                    <template x-for="option in genderOptions" :key="`gender-${option}`">
                                        <option :value="option" x-text="option"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm text-slate-500">年齢</label>
                                <select name="age" x-model="form.age" required
                                        class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3">
                                    <option value="" disabled selected>選択してください</option>
                                    <template x-for="option in ageOptions" :key="`age-${option}`">
                                        <option :value="option" x-text="option"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Body metrics -->
                <template x-if="step === 9">
                    <div>
                        <h2 class="text-xl font-semibold mb-4">身長と体重を入力してください</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-sm text-slate-500">身長</label>
                                <select name="height" x-model="form.height"
                                        class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3">
                                    <option value="" disabled x-bind:selected="form.height === ''">選択してください</option>
                                    <template x-for="option in heightOptions" :key="`height-${option.value}`">
                                        <option :value="option.value" x-text="option.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm text-slate-500">体重</label>
                                <select name="weight" x-model="form.weight"
                                        class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3">
                                    <option value="" disabled x-bind:selected="form.weight === ''">選択してください</option>
                                    <template x-for="option in weightOptions" :key="`weight-${option.value}`">
                                        <option :value="option.value" x-text="option.label"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-slate-400">目安で構いません。該当する選択肢からお選びください。</p>
                    </div>
                </template>

                <!-- Bio -->
                <template x-if="step === 10">
                    <div>
                        <h2 class="text-xl font-semibold mb-4">自己紹介文を記載しましょう！</h2>
                        <textarea name="bio" x-model="form.bio" maxlength="200" required
                                  class="w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3 min-h-[140px]"></textarea>
                        <p class="text-xs text-slate-500 mt-2">200文字以内</p>
                    </div>
                </template>

                <!-- Confirm -->
                <template x-if="step === 11">
                    <div class="space-y-6">
                        <h2 class="text-xl font-semibold">こちらの内容で登録しますか？</h2>
                        <div class="space-y-4 bg-slate-50/60 border border-slate-300 rounded-2xl px-5 py-6 text-sm">
                            <div class="flex justify-between"><span class="text-slate-500">ニックネーム</span><span x-text="form.nickname"></span></div>
                            <div class="flex justify-between"><span class="text-slate-500">地域</span><span x-text="`${form.region} ${form.city}`"></span></div>
                            <div class="flex justify-between"><span class="text-slate-500">性別</span><span x-text="form.gender"></span></div>
                            <div class="flex justify-between"><span class="text-slate-500">年齢</span><span x-text="form.age ? `${form.age}歳` : '-'" ></span></div>
                            <div>
                                <p class="text-slate-500">免許</p>
                                <ul class="mt-2 space-y-1">
                                    <template x-for="license in form.license_type" :key="'summary-' + license">
                                        <li class="flex justify-between">
                                            <span x-text="license"></span>
                                            <span x-text="form.license_years_meta[license]"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <template x-if="showCarFrequency || form.driving_frequency_car">
                                <div class="flex justify-between"><span class="text-slate-500">運転頻度（車）</span><span x-text="form.driving_frequency_car || '-'" ></span></div>
                            </template>
                            <template x-if="showBikeFrequency || form.driving_frequency_bike">
                                <div class="flex justify-between"><span class="text-slate-500">運転頻度（バイク）</span><span x-text="form.driving_frequency_bike || '-'" ></span></div>
                            </template>
                            <div>
                                <p class="text-slate-500">所有車両</p>
                                <template x-if="form.owns_none || form.owned_vehicles.length === 0">
                                    <p class="mt-2 text-sm text-slate-600">所有していません</p>
                                </template>
                                <template x-if="!form.owns_none && form.owned_vehicles.length">
                                    <ul class="mt-2 space-y-1 text-sm">
                                        <template x-for="(vehicle, index) in form.owned_vehicles" :key="'summary-owned-' + index">
                                            <li x-text="ownedVehicleSummary(vehicle)"></li>
                                        </template>
                                    </ul>
                                </template>
                            </div>
                            <div class="flex justify-between"><span class="text-slate-500">身長 / 体重</span><span x-text="formatMetrics()"></span></div>
                            <div>
                                <p class="text-slate-500 mb-2">自己紹介</p>
                                <p class="whitespace-pre-line" x-text="form.bio"></p>
                            </div>
                        </div>
                        <div class="flex gap-4 flex-col sm:flex-row">
                            <button type="button" @click="goToEdit()"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-600 hover:bg-slate-50 transition">
                                編集する
                            </button>
                            <button type="submit"
                                    class="w-full rounded-xl bg-emerald-400 text-slate-900 font-semibold px-4 py-3 hover:bg-emerald-300 transition">
                                登録する
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Navigation buttons -->
            <div class="mt-8 flex items-center justify-between" x-show="step >= 2 && step < 11">
                <button type="button" @click="prev" class="text-slate-600 hover:text-white transition" x-show="step > 2">戻る</button>
                <div class="flex-1"></div>
                <button type="button" @click="next" class="ml-auto inline-flex items-center gap-2 rounded-xl bg-emerald-400 text-slate-900 font-semibold px-6 py-3 hover:bg-emerald-300 transition">
                    次へ
                </button>
            </div>
            <p class="mt-3 text-xs text-rose-500" x-show="step >= 2 && step < 11 && stepError" x-text="stepError"></p>

            <!-- Hidden payload to keep values when steps are collapsed -->
            <div class="hidden">
                <input type="hidden" name="nickname" :value="form.nickname">
                <input type="hidden" name="prefecture_id" :value="form.prefecture_id">
                <input type="hidden" name="region" :value="form.region">
                <input type="hidden" name="city" :value="form.city">
                <input type="hidden" name="gender" :value="form.gender">
                <input type="hidden" name="age" :value="form.age">

                <template x-for="license in form.license_type" :key="'hidden-license-' + license">
                    <input type="hidden" name="license_type[]" :value="license">
                </template>

                <template x-for="([license, value], index) in licenseYearEntries" :key="'hidden-license-years-' + license + '-' + index">
                    <input type="hidden" :name="`license_years_meta[${license}]`" :value="value">
                </template>

                <input type="hidden" name="license_years_meta_json" :value="licenseYearsJson">

                <input type="hidden" name="driving_frequency_car" :value="showCarFrequency || drivingLocked ? form.driving_frequency_car : ''">
                <input type="hidden" name="driving_frequency_bike" :value="showBikeFrequency || drivingLocked ? form.driving_frequency_bike : ''">

                <input type="hidden" name="owns_none" :value="form.owns_none ? 1 : 0">
                <input type="hidden" name="owned_vehicles" :value="JSON.stringify(form.owned_vehicles)">

                <input type="hidden" name="height" :value="form.height">
                <input type="hidden" name="weight" :value="form.weight">
                <input type="hidden" name="bio" :value="form.bio">
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('profileWizard', ({ prefectures, licenseTypes, licenseYearOptions, drivingFrequencyOptions, carMakerGroups, bikeMakerGroups, carLicenseTypes, bikeLicenseTypes, profile, genderOptions, heightOptions, weightOptions }) => ({
            step: 0,
            prefectures,
            licenseTypes,
            licenseYearOptions,
            drivingFrequencyOptions,
            carMakerGroups,
            bikeMakerGroups,
            carLicenseTypes,
            bikeLicenseTypes,
            genderOptions,
            heightOptions,
            weightOptions,
            ageOptions: Array.from({ length: 83 }, (_, index) => String(index + 18)),
            cities: [],
            ownedVehicleFormVisible: false,
            ownedVehicleError: '',
            stepError: '',
            newOwnedVehicle: {
                type: 'car',
                maker: '',
                maker_custom: '',
                model: '',
            },
            form: {
                nickname: '',
                prefecture_id: '',
                region: '',
                city: '',
                gender: '',
                age: '',
                license_type: [],
                license_years_meta: {},
                driving_frequency_car: '',
                driving_frequency_bike: '',
                owned_vehicles: [],
                owns_none: false,
                height: '',
                weight: '',
                bio: '',
            },
            drivingLocked: false,
            get currentMakerGroups() {
                return this.newOwnedVehicle.type === 'car' ? this.carMakerGroups : this.bikeMakerGroups;
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
                const total = 11;
                return Math.min((this.step / total) * 100, 100);
            },
            init() {
                if (profile) {
                    this.form.nickname = profile.nickname ?? '';
                    this.form.region = profile.region ?? '';
                    this.form.city = profile.city ?? '';
                    this.form.gender = profile.gender ?? '';
                    this.form.age = profile.age ?? '';
                    if (this.form.age !== '' && this.form.age !== null) {
                        this.form.age = String(this.form.age);
                    }
                    this.form.license_type = profile.license_type ?? [];
                    this.form.license_years_meta = profile.license_years_meta ?? {};
                    this.form.driving_frequency_car = profile.driving_frequency_car ?? '';
                    this.form.driving_frequency_bike = profile.driving_frequency_bike ?? '';
                    this.form.owned_vehicles = Array.isArray(profile.owned_vehicles) ? profile.owned_vehicles : [];
                    this.form.owns_none = false;
                    this.form.height = profile.height !== null && profile.height !== undefined
                        ? String(profile.height)
                        : '';
                    this.form.weight = profile.weight !== null && profile.weight !== undefined
                        ? String(profile.weight)
                        : '';
                    this.form.bio = profile.bio ?? '';

                    if (this.form.region) {
                        const selected = this.prefectures.find((p) => p.name === this.form.region);
                        if (selected) {
                            this.form.prefecture_id = selected.id;
                            this.handlePrefectureChange().then(() => {
                                if (! this.cities.find((city) => city.name === this.form.city)) {
                                    this.form.city = '';
                                }
                            });
                        }
                    }
                }

                this.$watch('form.license_type', (value) => {
                    Object.keys(this.form.license_years_meta).forEach((key) => {
                        if (! value.includes(key)) {
                            delete this.form.license_years_meta[key];
                        }
                    });
                    this.enforceLicenseConsistency();
                });

                setTimeout(() => { this.step = 1; }, 1600);
                setTimeout(() => { this.step = 2; }, 3200);
                this.enforceLicenseConsistency();
            },
            async handlePrefectureChange() {
                const selected = this.prefectures.find((p) => p.id == this.form.prefecture_id);
                this.form.region = selected ? selected.name : '';
                this.form.city = '';
                this.cities = [];

                if (! this.form.prefecture_id) {
                    return;
                }

                try {
                    const response = await fetch(`/api/prefectures/${this.form.prefecture_id}/cities`);
                    if (response.ok) {
                        this.cities = await response.json();
                    }
                } catch (error) {
                    console.error('Failed to load cities', error);
                }
            },
            next() {
                this.stepError = '';

                if (this.step === 2 && ! this.form.nickname) {
                    this.setStepError('ニックネームを入力してください。');
                    return;
                }
                if (this.step === 3 && (! this.form.region || ! this.form.city)) {
                    this.setStepError('地域と市区町村を選択してください。');
                    return;
                }
                if (this.step === 4 && this.form.license_type.length === 0) {
                    this.setStepError('該当する免許を少なくとも1つ選択してください。');
                    return;
                }
                if (this.step === 5) {
                    for (const license of this.form.license_type) {
                        if (! this.form.license_years_meta[license]) {
                            this.setStepError('各免許の取得年数を選択してください。');
                            return;
                        }
                    }
                }
                if (this.step === 6 && ! this.drivingLocked) {
                    if (this.requiresCarFrequency() && ! this.form.driving_frequency_car) {
                        this.setStepError('車の運転頻度を選択してください。');
                        return;
                    }
                    if (this.requiresBikeFrequency() && ! this.form.driving_frequency_bike) {
                        this.setStepError('バイクの運転頻度を選択してください。');
                        return;
                    }
                }
                if (this.step === 7) {
                    if (this.ownedVehicleFormVisible) {
                        if (! this.saveOwnedVehicle()) {
                            this.setStepError(this.ownedVehicleError || '所有車両の情報を入力してください。');
                            return;
                        }
                    }

                    if (! this.form.owns_none && this.form.owned_vehicles.length === 0) {
                        this.ownedVehicleError = '所有車両を追加するか「所有していない」を選択してください。';
                        this.setStepError(this.ownedVehicleError);
                        return;
                    }

                    this.ownedVehicleError = '';
                }
                if (this.step === 8) {
                    if (! this.form.gender) {
                        this.setStepError('性別を選択してください。');
                        return;
                    }
                    if (! this.form.age) {
                        this.setStepError('年齢を選択してください。');
                        return;
                    }
                }
                if (this.step === 10 && ! this.form.bio) {
                    this.setStepError('自己紹介を入力してください。');
                    return;
                }

                this.step = Math.min(this.step + 1, 11);
            },
            prev() {
                this.stepError = '';
                this.step = Math.max(this.step - 1, 2);
            },
            startAddOwnedVehicle() {
                this.form.owns_none = false;
                this.resetOwnedVehicleForm();
                this.ownedVehicleFormVisible = true;
                this.ownedVehicleError = '';
            },
            resetOwnedVehicleForm() {
                this.newOwnedVehicle = {
                    type: 'car',
                    maker: '',
                    maker_custom: '',
                    model: '',
                };
            },
            setOwnedVehicleType(type) {
                this.newOwnedVehicle.type = type;
                this.newOwnedVehicle.maker = '';
                this.newOwnedVehicle.maker_custom = '';
            },
            cancelOwnedVehicleForm() {
                this.ownedVehicleFormVisible = false;
                this.ownedVehicleError = '';
            },
            saveOwnedVehicle() {
                const maker = this.newOwnedVehicle.maker;
                const model = this.newOwnedVehicle.model.trim();
                const makerCustom = this.newOwnedVehicle.maker_custom.trim();

                this.ownedVehicleError = '';

                if (! maker) {
                    this.ownedVehicleError = 'メーカーを選択してください。';
                    return false;
                }

                if (! model) {
                    this.ownedVehicleError = '車種名を入力してください。';
                    return false;
                }

                if (maker === 'その他' && makerCustom.length === 0) {
                    this.ownedVehicleError = 'その他を選んだ場合はメーカー名を入力してください。';
                    return false;
                }

                this.form.owned_vehicles = [
                    ...this.form.owned_vehicles,
                    {
                        type: this.newOwnedVehicle.type,
                        maker,
                        maker_custom: maker === 'その他' ? makerCustom : null,
                        model,
                    },
                ];

                this.ownedVehicleFormVisible = false;
                this.resetOwnedVehicleForm();
                this.ownedVehicleError = '';

                return true;
            },
            removeOwnedVehicle(index) {
                this.form.owned_vehicles.splice(index, 1);
            },
            setOwnsNone() {
                this.form.owned_vehicles = [];
                this.form.owns_none = true;
                this.ownedVehicleFormVisible = false;
                this.ownedVehicleError = '';
            },
            ownedVehicleSummary(vehicle) {
                const typeLabel = vehicle.type === 'car' ? '車' : 'バイク';
                const makerLabel = vehicle.maker === 'その他'
                    ? (vehicle.maker_custom || 'その他')
                    : vehicle.maker;

                return `${typeLabel} / ${makerLabel} / ${vehicle.model}`;
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
            setStepError(message) {
                const prefix = '未入力では次へ進めません。';
                this.stepError = message
                    ? `${prefix}${message}`
                    : prefix;
            },
            goToEdit() {
                this.step = 2;
            },
            toggleLicense(license, checked) {
                if (checked) {
                    if (license === '免許なし') {
                        this.form.license_type = ['免許なし'];
                    } else {
                        this.form.license_type = this.form.license_type.filter((item) => item !== '免許なし');
                        if (! this.form.license_type.includes(license)) {
                            this.form.license_type = [...this.form.license_type, license];
                        }
                    }
                } else {
                    this.form.license_type = this.form.license_type.filter((item) => item !== license);
                }
                this.enforceLicenseConsistency();
            },
            formatMetrics() {
                if (! this.form.height && ! this.form.weight) {
                    return '未入力';
                }
                const height = this.form.height ? this.lookupHeightLabel(this.form.height) : '-';
                const weight = this.form.weight ? this.lookupWeightLabel(this.form.weight) : '-';
                return `${height} / ${weight}`;
            },
            enforceLicenseConsistency() {
                const hasNoLicense = this.form.license_type.includes('免許なし');

                if (hasNoLicense) {
                    if (this.form.license_type.length !== 1 || this.form.license_type[0] !== '免許なし') {
                        this.form.license_type = ['免許なし'];
                    }
                    this.form.license_years_meta = { '免許なし': '免許なし' };
                    this.form.driving_frequency_car = '免許なし';
                    this.form.driving_frequency_bike = '免許なし';
                    this.drivingLocked = true;
                } else {
                    if (this.form.license_years_meta['免許なし']) {
                        delete this.form.license_years_meta['免許なし'];
                    }
                    if (this.form.driving_frequency_car === '免許なし') {
                        this.form.driving_frequency_car = '';
                    }
                    if (this.form.driving_frequency_bike === '免許なし') {
                        this.form.driving_frequency_bike = '';
                    }
                    this.drivingLocked = false;
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
        }));
    });
</script>
</body>
</html>
