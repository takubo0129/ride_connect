@php
    $isEditing = isset($editingVehicle);
    $existingImages = $existingImages ?? [];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="rc-glow-tag">{{ $isEditing ? 'Update' : 'New' }}</div>
        <h2 class="mt-2 text-3xl font-semibold text-slate-900 leading-tight">
            {{ $isEditing ? 'コネクト ─ 車両編集' : 'コネクト ─ 車両登録' }}
        </h2>
    </x-slot>

    <div class="rc-shell pb-36 pt-4">
        <div class="max-w-4xl mx-auto px-4">
            <div
                x-data="vehicleWizard({
                    prefectures: {{ $prefectures->toJson() }},
                    carBodyTypes: {{ json_encode($carBodyTypes, JSON_UNESCAPED_UNICODE) }},
                    carMakerGroups: {{ json_encode($carMakerGroups, JSON_UNESCAPED_UNICODE) }},
                    carDrivetrains: {{ json_encode($carDrivetrains, JSON_UNESCAPED_UNICODE) }},
                    carSeatOptions: {{ json_encode($carSeatOptions, JSON_UNESCAPED_UNICODE) }},
                    bikeDisplacementCategories: {{ json_encode($bikeDisplacementCategories, JSON_UNESCAPED_UNICODE) }},
                    bikeBodyTypes: {{ json_encode($bikeBodyTypes, JSON_UNESCAPED_UNICODE) }},
                    bikeMakerGroups: {{ json_encode($bikeMakerGroups, JSON_UNESCAPED_UNICODE) }},
                    bikeDrivetrains: {{ json_encode($bikeDrivetrains, JSON_UNESCAPED_UNICODE) }},
                    sharingPeriods: {{ json_encode($sharingPeriods, JSON_UNESCAPED_UNICODE) }},
                    missions: {{ json_encode($missions, JSON_UNESCAPED_UNICODE) }},
                    oldValues: {{ json_encode($oldValues, JSON_UNESCAPED_UNICODE) }},
                    existingImages: @js($existingImages),
                    isEditing: @js($isEditing)
                })"
                x-init="init()"
                class="relative vehicle-wizard"
            >
                <form method="POST" action="{{ $isEditing ? route('vehicles.update', $editingVehicle) : route('vehicles.store') }}" enctype="multipart/form-data"
                      class="rc-card p-8 sm:p-12 overflow-hidden">
                    @csrf
                    @if ($isEditing)
                        @method('PUT')
                    @endif

                    <input type="file" name="photos[]" multiple accept="image/*" capture="environment"
                           class="hidden" x-ref="photoInput" @change="handlePhotoChange($event)">
                    <input type="hidden" name="existing_images" :value="JSON.stringify(existingPhotos.map(photo => photo.path))">

                    <div class="absolute top-0 left-0 h-1 w-full overflow-hidden rounded-full bg-slate-200/70">
                        <div class="h-full bg-gradient-to-r from-emerald-400 via-emerald-500 to-sky-400 transition-all duration-500" :style="`width: ${progressPercentage}%`"></div>
                    </div>

                    <div class="min-h-[360px] mt-6 space-y-10">
                        <section x-show="currentStepKey === 'intro'" x-cloak class="text-center space-y-4">
                            <div class="inline-flex h-24 w-24 items-center justify-center rounded-full border border-emerald-400 text-xl font-semibold">コネクト</div>
                            <p class="text-slate-600">あなたの大切な車やバイクを、同じ熱量を持つ人へ届けましょう。</p>
                        </section>

                        <section x-show="currentStepKey === 'type'" x-cloak class="space-y-6">
                            <div class="text-center space-y-2">
                                <h2 class="text-xl font-semibold">登録する車種を選択してください</h2>
                                <p class="text-sm text-slate-500">後からの変更も可能です。</p>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <button type="button"
                                        class="rc-option-tile"
                                        :class="form.type === 'car' ? 'rc-option-tile-active' : ''"
                                        @click="setType('car')">
                                    <span class="block text-lg font-semibold">車を登録する</span>
                                    <span class="mt-2 block text-sm text-slate-500">ボディタイプや駆動方式、乗車定員などを設定できます。</span>
                                </button>
                                <button type="button"
                                        class="rc-option-tile"
                                        :class="form.type === 'bike' ? 'rc-option-tile-active' : ''"
                                        @click="setType('bike')">
                                    <span class="block text-lg font-semibold">バイクを登録する</span>
                                    <span class="mt-2 block text-sm text-slate-500">排気量カテゴリやシート高など、バイクならではの項目を設定します。</span>
                                </button>
                            </div>
                        </section>

                        <section x-show="currentStepKey === 'region'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">受け渡し地域を教えてください</h2>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="prefecture_id" class="text-sm text-slate-500">都道府県</label>
                                    <select id="prefecture_id" name="prefecture_id" x-model="form.prefecture_id"
                                            @change="handlePrefectureChange"
                                            class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3" required>
                                        <option value="">選択してください</option>
                                        <template x-for="prefecture in prefectures" :key="prefecture.id">
                                            <option :value="prefecture.id" x-text="prefecture.name"></option>
                                        </template>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('prefecture_id')" />
                                </div>
                                <div>
                                    <label for="city_id" class="text-sm text-slate-500">市区町村</label>
                                    <select id="city_id" name="city_id" x-model="form.city_id"
                                            class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3" required>
                                        <option value="">都道府県を選択してください</option>
                                        <template x-for="city in cities" :key="city.id">
                                            <option :value="city.id" x-text="city.name"></option>
                                        </template>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('city_id')" />
                                </div>
                            </div>
                        </section>

                        <section x-show="currentStepKey === 'car_body'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">ボディタイプを選択してください</h2>
                            <p class="text-sm text-slate-500">登録後の検索条件にも活用されます。</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <template x-for="body in carBodyTypes" :key="body">
                                    <button type="button"
                                            class="rounded-xl border px-4 py-3 text-left transition"
                                            :class="form.body_type_name === body ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'"
                                            :aria-pressed="form.body_type_name === body ? 'true' : 'false'"
                                            @click="selectSingleBody(body)">
                                        <span x-text="body"></span>
                                    </button>
                                </template>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('body_type')" />
                        </section>

                        <section x-show="currentStepKey === 'car_maker'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">メーカーを選択してください</h2>
                            <div>
                                <label class="text-sm text-slate-500">メーカー</label>
                                <select name="maker" x-model="form.maker"
                                        :required="form.type === 'car'"
                                        :disabled="form.type !== 'car'"
                                        class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3">
                                    <option value="">選択してください</option>
                                    <template x-for="group in carMakerGroups" :key="group.label">
                                        <optgroup :label="group.label">
                                            <template x-for="maker in group.options" :key="maker">
                                                <option :value="maker" x-text="maker"></option>
                                            </template>
                                        </optgroup>
                                    </template>
                                </select>
                            </div>
                            <div x-show="form.maker === 'その他'" class="space-y-2">
                                <label class="text-sm text-slate-500" for="maker_custom">メーカー名（自由入力）</label>
                                <input type="text" id="maker_custom" name="maker_custom" x-model="form.maker_custom"
                                       :disabled="form.type !== 'car'"
                                       class="w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3"
                                       maxlength="100" placeholder="メーカー名を入力" />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('maker')" />
                            <x-input-error class="mt-2" :messages="$errors->get('maker_custom')" />
                        </section>

                        <section x-show="currentStepKey === 'car_transmission'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">ミッションを選択してください</h2>
                            <div class="flex flex-wrap gap-3">
                                <template x-for="mission in missions" :key="mission">
                                    <button type="button"
                                            class="rounded-xl border px-6 py-2 text-sm transition"
                                            :class="form.transmission === mission ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'"
                                            @click="form.transmission = mission">
                                        <span x-text="mission"></span>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="transmission" :value="form.transmission" :disabled="form.type !== 'car'">
                            <x-input-error class="mt-2" :messages="$errors->get('transmission')" />
                        </section>

                        <section x-show="currentStepKey === 'car_drive'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">駆動方式を選択してください</h2>
                            <div class="flex flex-wrap gap-3">
                                <template x-for="drive in carDrivetrains" :key="drive">
                                    <button type="button"
                                            class="rounded-xl border px-6 py-2 text-sm transition"
                                            :class="form.drivetrain === drive ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'"
                                            @click="form.drivetrain = drive">
                                        <span x-text="drive"></span>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="drivetrain" :value="form.drivetrain" :disabled="form.type !== 'car'">
                            <x-input-error class="mt-2" :messages="$errors->get('drivetrain')" />
                        </section>

                        <section x-show="currentStepKey === 'car_seats'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">乗車定員を選択してください</h2>
                            <div class="flex flex-wrap gap-3">
                                <template x-for="seat in carSeatOptions" :key="seat">
                                    <button type="button"
                                            class="rounded-xl border px-5 py-2 text-sm transition"
                                            :class="form.seats_choice === seat ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'"
                                            @click="form.seats_choice = seat">
                                        <span x-text="seat === '9+' ? '9人以上' : `${seat}人`"></span>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="seats_choice" :value="form.seats_choice" :disabled="form.type !== 'car'">
                            <x-input-error class="mt-2" :messages="$errors->get('seats_choice')" />
                        </section>

                        <section x-show="currentStepKey === 'bike_displacement'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">排気量を選択してください</h2>
                            <p class="text-sm text-slate-500">バイクに最も近い排気量カテゴリを1つ選択してください。</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <template x-for="category in bikeDisplacementCategories" :key="category">
                                    <button type="button"
                                            class="rounded-xl border px-4 py-3 text-left transition"
                                            :class="form.displacement_categories[0] === category ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'"
                                            :aria-pressed="form.displacement_categories[0] === category ? 'true' : 'false'"
                                            @click="selectDisplacementCategory(category)">
                                        <span x-text="category"></span>
                                    </button>
                                </template>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('displacement_categories')" />
                        </section>

                        <section x-show="currentStepKey === 'bike_body'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">ボディタイプを選択してください</h2>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <template x-for="body in bikeBodyTypes" :key="body">
                                    <button type="button"
                                            class="rounded-xl border px-4 py-3 text-left transition"
                                            :class="form.body_type_name === body ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'"
                                            :aria-pressed="form.body_type_name === body ? 'true' : 'false'"
                                            @click="selectSingleBody(body)">
                                        <span x-text="body"></span>
                                    </button>
                                </template>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('body_type')" />
                        </section>

                        <section x-show="currentStepKey === 'bike_maker'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">メーカーを選択してください</h2>
                            <div>
                                <label class="text-sm text-slate-500">メーカー</label>
                                <select name="maker" x-model="form.maker"
                                        :required="form.type === 'bike'"
                                        :disabled="form.type !== 'bike'"
                                        class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3">
                                    <option value="">選択してください</option>
                                    <template x-for="group in bikeMakerGroups" :key="group.label">
                                        <optgroup :label="group.label">
                                            <template x-for="maker in group.options" :key="maker">
                                                <option :value="maker" x-text="maker"></option>
                                            </template>
                                        </optgroup>
                                    </template>
                                </select>
                            </div>
                            <div x-show="form.maker === 'その他'" class="space-y-2">
                                <label class="text-sm text-slate-500" for="maker_custom_bike">メーカー名（自由入力）</label>
                                <input type="text" id="maker_custom_bike" name="maker_custom" x-model="form.maker_custom"
                                       :disabled="form.type !== 'bike'"
                                       class="w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3"
                                       maxlength="100" placeholder="メーカー名を入力" />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('maker')" />
                            <x-input-error class="mt-2" :messages="$errors->get('maker_custom')" />
                        </section>

                        <section x-show="currentStepKey === 'bike_transmission'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">ミッションを選択してください</h2>
                            <div class="flex flex-wrap gap-3">
                                <template x-for="mission in missions" :key="mission">
                                    <button type="button"
                                            class="rounded-xl border px-6 py-2 text-sm transition"
                                            :class="form.transmission === mission ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'"
                                            @click="form.transmission = mission">
                                        <span x-text="mission"></span>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="transmission" :value="form.transmission" :disabled="form.type !== 'bike'">
                            <x-input-error class="mt-2" :messages="$errors->get('transmission')" />
                        </section>

                        <section x-show="currentStepKey === 'bike_drive'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">駆動方式を選択してください</h2>
                            <p class="text-sm text-slate-500">（チェーン / ベルト / シャフト）</p>
                            <div class="flex flex-wrap gap-3">
                                <template x-for="drive in bikeDrivetrains" :key="drive">
                                    <button type="button"
                                            class="rounded-xl border px-6 py-2 text-sm transition"
                                            :class="form.drivetrain === drive ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'"
                                            @click="form.drivetrain = drive">
                                        <span x-text="drive"></span>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="drivetrain" :value="form.drivetrain" :disabled="form.type !== 'bike'">
                            <x-input-error class="mt-2" :messages="$errors->get('drivetrain')" />
                        </section>

                        <section x-show="currentStepKey === 'bike_metrics'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">重量とシート高を入力してください</h2>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm text-slate-500" for="weight">車両重量 (kg)</label>
                                    <input type="number" id="weight" name="weight" x-model="form.weight"
                                           :required="form.type === 'bike'"
                                           :disabled="form.type !== 'bike'"
                                           class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3"
                                           min="50" max="500" step="1" placeholder="例）210" />
                                </div>
                                <div>
                                    <label class="text-sm text-slate-500" for="seat_height">シート高 (mm)</label>
                                    <input type="number" id="seat_height" name="seat_height" x-model="form.seat_height"
                                           :required="form.type === 'bike'"
                                           :disabled="form.type !== 'bike'"
                                           class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3"
                                           min="600" max="1000" step="5" placeholder="例）820" />
                                </div>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('weight')" />
                            <x-input-error class="mt-2" :messages="$errors->get('seat_height')" />
                        </section>

                        <section x-show="currentStepKey === 'common_model'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">車種名を入力してください</h2>
                            <p class="text-sm text-slate-500">100文字以内で入力してください。検索画面での表示に利用されます。</p>
                            <input type="text" name="model" x-model="form.model" maxlength="100"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3"
                                   :placeholder="form.type === 'bike' ? '例）CB400SF' : '例）GR86'" required />
                            <x-input-error class="mt-2" :messages="$errors->get('model')" />
                        </section>

                        <section x-show="currentStepKey === 'common_fuel'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">給油する燃料を選択してください</h2>
                            <div class="flex flex-wrap gap-3">
                                <template x-for="fuel in fuelOptions" :key="fuel.value">
                                    <button type="button"
                                            class="rounded-xl border px-5 py-2 text-sm transition"
                                            :class="form.fuel_type === fuel.value ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'"
                                            @click="selectFuelType(fuel.value)">
                                        <span x-text="fuel.label"></span>
                                    </button>
                                </template>
                            </div>
                            <div x-show="form.fuel_type === 'その他'" class="space-y-2">
                                <label class="text-sm text-slate-500" for="fuel_type_other">燃料の種類（自由入力）</label>
                                <input type="text" id="fuel_type_other" name="fuel_type_other" x-model="form.fuel_type_other" maxlength="100"
                                       class="w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3"
                                       placeholder="例）混合燃料など" />
                            </div>
                            <input type="hidden" name="fuel_type" :value="form.fuel_type">
                            <x-input-error class="mt-2" :messages="$errors->get('fuel_type')" />
                            <x-input-error class="mt-2" :messages="$errors->get('fuel_type_other')" />
                        </section>

                        <section x-show="currentStepKey === 'common_photos'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">車両写真を追加してください</h2>
                            <p class="text-sm text-slate-500">最大20枚まで選択できます。傷や状態が分かる写真をアップロードしましょう。</p>
                            <div class="border-2 border-dashed border-slate-300 rounded-2xl bg-slate-50 px-6 py-8 text-center transition hover:border-emerald-400"
                                 @dragover.prevent
                                 @drop.prevent="handlePhotoDrop($event)">
                                <div class="space-y-3">
                                    <div class="text-sm text-slate-600">写真をドラッグ＆ドロップ、または</div>
                                    <button type="button" class="rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-400"
                                            @click="$refs.photoInput.click()">ファイルを選択</button>
                                    <div class="text-xs text-slate-400">アップロード済み <span x-text="existingPhotos.length + photoFiles.length"></span> / 20</div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <template x-for="(photo, index) in existingPhotos" :key="`existing-${photo.path}`">
                                    <div class="relative rounded-xl overflow-hidden border border-slate-300 bg-white shadow-sm">
                                        <img :src="photo.url" alt="登録済みの写真" class="h-32 w-full object-cover">
                                        <button type="button"
                                                class="absolute top-2 right-2 rounded-full bg-white/80 p-1 text-slate-600 hover:text-rose-500"
                                                x-show="isEditing"
                                                @click="removeExistingPhoto(index)">&times;</button>
                                    </div>
                                </template>

                                <template x-for="(preview, index) in photoPreviews" :key="`new-${index}`">
                                    <div class="relative rounded-xl overflow-hidden border border-slate-300 bg-white shadow-sm">
                                        <img :src="preview" alt="選択した写真" class="h-32 w-full object-cover">
                                        <button type="button" class="absolute top-2 right-2 rounded-full bg-white/80 p-1 text-slate-600 hover:text-rose-500"
                                                @click="removePhoto(index)">&times;</button>
                                    </div>
                                </template>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('photos')" />
                            <x-input-error class="mt-2" :messages="$errors->get('photos.*')" />
                        </section>

                        <section x-show="currentStepKey === 'common_year'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">年式を入力してください</h2>
                            <p class="text-sm text-slate-500">1980年〜2025年の範囲で設定できます。</p>
                            <input type="number" name="year" x-model="form.year" min="1980" max="2025"
                                   class="w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3"
                                   placeholder="例）2021" required />
                            <x-input-error class="mt-2" :messages="$errors->get('year')" />
                        </section>

                        <section x-show="currentStepKey === 'common_sharing'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">シェアリングプランを選択してください</h2>
                            <p class="text-sm text-slate-500">提示したいプランを1つだけ選びます。料金や受け渡し条件とセットで利用者に表示されます。</p>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                <template x-for="period in sharingPeriods" :key="period">
                                    <button type="button"
                                            class="rounded-xl border px-4 py-2 text-sm transition"
                                            :class="form.sharing_periods[0] === period ? 'border-emerald-500 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-slate-50 hover:bg-slate-100'"
                                            @click="selectSharingPlan(period)">
                                        <span x-text="period"></span>
                                    </button>
                                </template>
                            </div>
                        </section>

                        <section x-show="currentStepKey === 'common_price'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">料金を設定してください</h2>
                            <p class="text-sm text-slate-500">1日あたりの料金を「万の位」と「千の位」から選択します（1,000円〜100,000円）。</p>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm text-slate-500">万の位</label>
                                    <select x-model="form.priceTenThousands" @change="updatePrice"
                                            class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 focus:border-emerald-400 focus:ring-0">
                                        <option value="">選択してください</option>
                                        <template x-for="option in priceTenThousandsOptions" :key="`ten-${option}`">
                                            <option :value="option" x-text="`${option} 万`"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm text-slate-500">千の位</label>
                                    <select x-model="form.priceThousands" @change="updatePrice"
                                            class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 focus:border-emerald-400 focus:ring-0">
                                        <option value="">選択してください</option>
                                        <template x-for="option in priceThousandsOptions" :key="`thousand-${option}`">
                                            <option :value="option" x-text="`${option} 千`"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                選択中の料金：<span class="font-semibold" x-text="form.price ? `¥${Number(form.price).toLocaleString()}` : '未選択'"></span>
                            </div>
                            <input type="hidden" name="price" :value="form.price">
                            <x-input-error class="mt-2" :messages="$errors->get('price')" />
                        </section>

                        <section x-show="currentStepKey === 'common_notes'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">シェアリング時の注意事項を記載してください</h2>
                            <p class="text-sm text-slate-500">各項目は任意です。必要なものだけ入力していただければ構いません。</p>
                            <div class="grid gap-4">
                                <div>
                                    <label class="text-sm font-semibold text-slate-600" for="caution_operation">操作に関して</label>
                                    <textarea id="caution_operation" name="caution_operation" x-model="form.caution_operation" rows="3" maxlength="500"
                                              class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3"
                                              placeholder="例）スポーツモードの切り替えは停車時に行ってください。"></textarea>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-600" for="caution_driving">走行に関して</label>
                                    <textarea id="caution_driving" name="caution_driving" x-model="form.caution_driving" rows="3" maxlength="500"
                                              class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3"
                                              placeholder="例）高速での連続走行は100km/h以内でお願いします。"></textarea>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-600" for="caution_capacity">乗車定員に関して</label>
                                    <textarea id="caution_capacity" name="caution_capacity" x-model="form.caution_capacity" rows="3" maxlength="500"
                                              class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3"
                                              placeholder="例）荷物込みで4人以内でお願いします。"></textarea>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-600" for="caution_return">返却に関して</label>
                                    <textarea id="caution_return" name="caution_return" x-model="form.caution_return" rows="3" maxlength="500"
                                              class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3"
                                              placeholder="例）返却時は満タンでお願いいたします。"></textarea>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-600" for="caution_refuel">給油に関して</label>
                                    <textarea id="caution_refuel" name="caution_refuel" x-model="form.caution_refuel" rows="3" maxlength="500"
                                              class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3"
                                              placeholder="例）ハイオク指定です。返却前に満タンにしてください。"></textarea>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-600" for="caution_other">その他の注意事項</label>
                                    <textarea id="caution_other" name="caution_other" x-model="form.caution_other" rows="3" maxlength="500"
                                              class="mt-2 w-full rounded-xl bg-slate-50 border border-slate-300 focus:border-emerald-400 focus:ring-0 px-4 py-3"
                                              placeholder="例）ヘルメット・グローブは貸し出していません。"></textarea>
                                </div>
                            </div>
                        </section>

                        <section x-show="currentStepKey === 'review'" x-cloak class="space-y-6">
                            <h2 class="text-xl font-semibold">この内容で登録しますか？</h2>
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" x-show="reviewEditPanel">
                                編集したい項目を選択すると、該当の入力欄に戻って内容を更新できます。
                            </div>
                            <div class="space-y-4 rounded-2xl border border-slate-300 bg-slate-50 px-5 py-5 text-sm">
                                <div class="flex justify-between"><span class="text-slate-500">車種</span><span x-text="form.type === 'car' ? '車' : 'バイク'"></span></div>
                                <div class="flex justify-between"><span class="text-slate-500">地域</span><span x-text="reviewRegion"></span></div>
                                <div class="flex justify-between"><span class="text-slate-500">メーカー</span><span x-text="reviewMaker"></span></div>
                                <div class="flex justify-between"><span class="text-slate-500">車種名</span><span x-text="form.model"></span></div>
                                <div class="flex justify-between"><span class="text-slate-500">燃料</span><span x-text="reviewFuelType"></span></div>
                                <div class="flex justify-between"><span class="text-slate-500">年式</span><span x-text="form.year ? `${form.year}年` : '未入力'"></span></div>
                                <div class="flex justify-between"><span class="text-slate-500">ボディタイプ</span><span x-text="form.body_type_name || '未選択'"></span></div>
                                <template x-if="form.type === 'bike'">
                                    <div class="flex justify-between"><span class="text-slate-500">排気量</span><span x-text="form.displacement_categories.join(' / ')"></span></div>
                                </template>
                                <div class="flex justify-between"><span class="text-slate-500">ミッション</span><span x-text="form.transmission || '未選択'"></span></div>
                                <div class="flex justify-between"><span class="text-slate-500">駆動方式</span><span x-text="form.drivetrain || '未選択'"></span></div>
                                <template x-if="form.type === 'car'">
                                    <div class="flex justify-between"><span class="text-slate-500">乗車定員</span><span x-text="form.seats_choice ? (form.seats_choice === '9+' ? '9人以上' : `${form.seats_choice}人`) : '未選択'"></span></div>
                                </template>
                                <template x-if="form.type === 'bike'">
                                    <div class="flex justify-between"><span class="text-slate-500">重量 / シート高</span><span x-text="`${form.weight || '-'}kg / ${form.seat_height || '-'}mm`"></span></div>
                                </template>
                                <div class="flex justify-between"><span class="text-slate-500">シェアリングプラン</span><span x-text="form.sharing_periods.length ? form.sharing_periods[0] : '未選択'"></span></div>
                                <div class="flex justify-between"><span class="text-slate-500">料金</span><span x-text="form.price ? `¥${Number(form.price).toLocaleString()}` : '未設定'"></span></div>
                                <template x-if="existingPhotos.length || photoPreviews.length">
                                    <div class="pt-2">
                                        <div class="text-slate-500 mb-2">車両写真</div>
                                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                            <template x-for="photo in existingPhotos" :key="`review-existing-${photo.path}`">
                                                <img :src="photo.url" alt="登録済みの写真" class="h-24 w-full rounded-lg object-cover border border-slate-300">
                                            </template>
                                            <template x-for="preview in photoPreviews" :key="`review-new-${preview}`">
                                                <img :src="preview" alt="アップロード予定の写真" class="h-24 w-full rounded-lg object-cover border border-slate-300">
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="hasCautionNotes">
                                    <div class="pt-2">
                                        <div class="text-slate-500 mb-2">注意事項</div>
                                        <div class="space-y-2">
                                            <template x-for="entry in cautionEntries" :key="entry.label">
                                                <div class="rounded-xl border border-slate-300 bg-white px-4 py-3">
                                                    <div class="text-xs font-semibold text-slate-500" x-text="entry.label"></div>
                                                    <div class="mt-1 text-sm text-slate-700 whitespace-pre-line" x-text="entry.value"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div x-show="reviewEditPanel" x-cloak class="space-y-4 rounded-2xl border border-slate-300 bg-white px-5 py-5 text-sm">
                                <div class="text-slate-600">編集したい項目を選択してください</div>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="target in reviewEditTargets" :key="target.key">
                                        <button type="button"
                                                class="rounded-xl border border-slate-300 px-4 py-2 text-sm text-slate-600 transition hover:border-emerald-400 hover:bg-emerald-50 hover:text-emerald-900"
                                                @click="jumpToStep(target.key)">
                                            <span x-text="target.label"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm text-rose-500" x-text="stepError"></div>
                        <div class="flex flex-wrap gap-3">
                            <button type="button"
                                    class="rounded-xl border border-slate-300 px-5 py-2 text-sm text-slate-600 hover:bg-slate-100"
                                    x-show="!isReviewStep && step > 0"
                                    @click="prev">
                                戻る
                            </button>

                            <button type="button" @click="next"
                                    class="rounded-xl bg-emerald-400 px-6 py-2 text-sm font-semibold text-slate-900 shadow hover:bg-emerald-300"
                                    x-show="!isReviewStep">
                                次へ
                            </button>

                            <button type="submit"
                                    class="rounded-xl bg-emerald-500 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-400"
                                    x-show="isReviewStep">
                                登録する
                            </button>

                            <button type="button"
                                    class="rounded-xl border border-emerald-500 px-6 py-2 text-sm font-semibold text-emerald-600 hover:bg-emerald-50"
                                    x-show="isReviewStep"
                                    @click="toggleReviewEditPanel">
                                <span x-text="reviewEditPanel ? '編集をやめる' : '編集する'"></span>
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="type" :value="form.type">
                    <input type="hidden" name="body_type" :value="JSON.stringify(form.body_type)">
                    <input type="hidden" name="displacement_categories" :value="JSON.stringify(form.displacement_categories)">
                    <input type="hidden" name="sharing_periods" :value="JSON.stringify(form.sharing_periods)">
                </form>
            </div>
        </div>
    </div>

    @include('partials.bottom-navigation')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('vehicleWizard', ({
                prefectures,
                carBodyTypes,
                carMakerGroups,
                carDrivetrains,
                carSeatOptions,
                bikeDisplacementCategories,
                bikeBodyTypes,
                bikeMakerGroups,
                bikeDrivetrains,
                sharingPeriods,
                missions,
                oldValues,
                existingImages = [],
                isEditing = false
            }) => ({
                step: oldValues.type ? 2 : 0,
                baseSteps: ['intro', 'type'],
                carSteps: ['region', 'car_body', 'car_maker', 'car_transmission', 'car_drive', 'car_seats', 'common_model', 'common_fuel', 'common_year', 'common_photos', 'common_sharing', 'common_price', 'common_notes', 'review'],
                bikeSteps: ['region', 'bike_displacement', 'bike_body', 'bike_maker', 'bike_transmission', 'bike_drive', 'bike_metrics', 'common_model', 'common_fuel', 'common_year', 'common_photos', 'common_sharing', 'common_price', 'common_notes', 'review'],
                fuelOptions: [
                    { value: 'レギュラー', label: 'レギュラー' },
                    { value: 'ハイオク', label: 'ハイオク' },
                    { value: '軽油', label: '軽油' },
                    { value: 'EV', label: 'EV' },
                    { value: '水素', label: '水素' },
                    { value: 'その他', label: 'その他' },
                ],
                priceTenThousandsOptions: Array.from({ length: 11 }, (_, index) => index),
                priceThousandsOptions: Array.from({ length: 10 }, (_, index) => index),
                prefectures,
                cities: [],
                carBodyTypes,
                carMakerGroups,
                carDrivetrains,
                carSeatOptions,
                bikeDisplacementCategories,
                bikeBodyTypes,
                bikeMakerGroups,
                bikeDrivetrains,
                sharingPeriods,
                missions,
                photoFiles: [],
                photoPreviews: [],
                maxPhotos: 20,
                stepError: '',
                reviewEditPanel: false,
                existingPhotos: existingImages,
                isEditing,
                form: {
                    type: oldValues.type || '',
                    prefecture_id: oldValues.prefecture_id || '',
                    city_id: oldValues.city_id || '',
                    maker: oldValues.maker || '',
                    maker_custom: oldValues.maker_custom || '',
                    model: oldValues.model || '',
                    fuel_type: oldValues.fuel_type || '',
                    fuel_type_other: oldValues.fuel_type_other || '',
                    year: oldValues.year || '',
                    body_type: Array.isArray(oldValues.body_type) && oldValues.body_type.length ? [oldValues.body_type[0]] : [],
                    body_type_name: Array.isArray(oldValues.body_type) && oldValues.body_type.length ? oldValues.body_type[0] : '',
                    displacement_categories: Array.isArray(oldValues.displacement_categories) && oldValues.displacement_categories.length ? [oldValues.displacement_categories[0]] : [],
                    transmission: oldValues.transmission || '',
                    drivetrain: oldValues.drivetrain || '',
                    seats_choice: oldValues.seats_choice || '',
                    weight: oldValues.weight || '',
                    seat_height: oldValues.seat_height || '',
                    priceTenThousands: '',
                    priceThousands: '',
                    price: oldValues.price || '',
                    sharing_periods: Array.isArray(oldValues.sharing_periods) && oldValues.sharing_periods.length ? [oldValues.sharing_periods[0]] : [],
                    caution_operation: oldValues.caution_operation || '',
                    caution_driving: oldValues.caution_driving || '',
                    caution_capacity: oldValues.caution_capacity || '',
                    caution_return: oldValues.caution_return || '',
                    caution_refuel: oldValues.caution_refuel || '',
                    caution_other: oldValues.caution_other || '',
                },

                init() {
                    if (this.form.prefecture_id) {
                        this.handlePrefectureChange(true);
                    }

                    if (this.form.price) {
                        const numericPrice = Number(this.form.price);
                        if (!Number.isNaN(numericPrice)) {
                            const ten = Math.floor(numericPrice / 10000);
                            const thou = Math.floor((numericPrice % 10000) / 1000);
                            this.form.priceTenThousands = String(ten);
                            this.form.priceThousands = String(thou);
                        }
                    }

                    this.updatePrice();
                },

                remainingPhotoSlots() {
                    return Math.max(this.maxPhotos - (this.photoFiles.length + this.existingPhotos.length), 0);
                },

                hasAnyPhotos() {
                    return this.photoFiles.length > 0 || this.existingPhotos.length > 0;
                },

                get stepsForType() {
                    if (this.form.type === 'car') {
                        return this.carSteps;
                    }
                    if (this.form.type === 'bike') {
                        return this.bikeSteps;
                    }
                    return this.baseSteps;
                },

                get currentStepKey() {
                    if (this.step === 0) return 'intro';
                    if (this.step === 1) return 'type';
                    const offset = this.step - 2;
                    const flow = this.form.type === 'bike' ? this.bikeSteps : this.carSteps;
                    return flow[offset] || 'review';
                },

                get progressPercentage() {
                    const flowLength = this.form.type === 'bike' ? this.bikeSteps.length : this.carSteps.length;
                    const total = this.form.type ? flowLength + 2 : this.baseSteps.length;
                    return Math.min((this.step / Math.max(total - 1, 1)) * 100, 100);
                },

                get isReviewStep() {
                    return this.currentStepKey === 'review';
                },

                get reviewRegion() {
                    const prefecture = this.prefectures.find(p => String(p.id) === String(this.form.prefecture_id));
                    const city = this.cities.find(c => String(c.id) === String(this.form.city_id));
                    return [prefecture?.name, city?.name].filter(Boolean).join(' ');
                },

                get reviewMaker() {
                    return (this.form.maker === 'その他' && this.form.maker_custom)
                        ? this.form.maker_custom
                        : (this.form.maker || '未選択');
                },

                get reviewFuelType() {
                    if (!this.form.fuel_type) {
                        return '未設定';
                    }
                    if (this.form.fuel_type === 'その他' && this.form.fuel_type_other) {
                        return this.form.fuel_type_other;
                    }
                    return this.form.fuel_type;
                },

                get reviewEditTargets() {
                    const commonTargets = [
                        { key: 'type', label: '車両タイプ' },
                        { key: 'region', label: '地域・市区町村' },
                        { key: 'common_model', label: '車種名' },
                        { key: 'common_fuel', label: '燃料' },
                        { key: 'common_year', label: '年式' },
                        { key: 'common_photos', label: '写真' },
                        { key: 'common_sharing', label: 'シェアリングプラン' },
                        { key: 'common_price', label: '料金設定' },
                        { key: 'common_notes', label: '注意事項' },
                    ];

                    const specificTargets = this.form.type === 'bike'
                        ? [
                            { key: 'bike_displacement', label: '排気量カテゴリ' },
                            { key: 'bike_body', label: 'ボディタイプ' },
                            { key: 'bike_maker', label: 'メーカー' },
                            { key: 'bike_transmission', label: 'ミッション' },
                            { key: 'bike_drive', label: '駆動方式' },
                            { key: 'bike_metrics', label: '重量・シート高' },
                          ]
                        : [
                            { key: 'car_body', label: 'ボディタイプ' },
                            { key: 'car_maker', label: 'メーカー' },
                            { key: 'car_transmission', label: 'ミッション' },
                            { key: 'car_drive', label: '駆動方式' },
                            { key: 'car_seats', label: '乗車定員' },
                          ];

                    return [...commonTargets, ...specificTargets].filter(target => this.stepExists(target.key));
                },

                get cautionEntries() {
                    return [
                        { label: '操作', value: this.form.caution_operation },
                        { label: '走行', value: this.form.caution_driving },
                        { label: '乗車定員', value: this.form.caution_capacity },
                        { label: '返却', value: this.form.caution_return },
                        { label: '給油', value: this.form.caution_refuel },
                        { label: 'その他', value: this.form.caution_other },
                    ].filter(entry => entry.value && entry.value.trim().length);
                },

                get hasCautionNotes() {
                    return this.cautionEntries.length > 0;
                },

                async handlePrefectureChange(preserve = false) {
                    const previousCityId = this.form.city_id;
                    this.form.city_id = '';
                    this.cities = [];

                    if (!this.form.prefecture_id) {
                        return;
                    }

                    try {
                        const response = await fetch(`/api/prefectures/${this.form.prefecture_id}/cities`);
                        if (response.ok) {
                            this.cities = await response.json();

                            const candidateId = preserve ? previousCityId : (previousCityId || oldValues.city_id);
                            const matched = this.cities.find(city => String(city.id) === String(candidateId));
                            if (matched) {
                                this.form.city_id = String(matched.id);
                            }
                        }
                    } catch (error) {
                        console.error('Failed to load cities', error);
                    }
                },

                setType(type) {
                    if (this.form.type !== type) {
                        this.form.body_type = [];
                        this.form.body_type_name = '';
                        this.form.maker = '';
                        this.form.maker_custom = '';
                        this.form.transmission = '';
                        this.form.drivetrain = '';
                        this.form.seats_choice = '';
                        this.form.displacement_categories = [];
                        this.form.weight = '';
                        this.form.seat_height = '';
                    }
                    this.form.type = type;
                    if (this.step < 2) {
                        this.step = 2;
                    }
                },

                handlePhotoChange(event) {
                    const files = Array.from(event.target?.files || []);
                    if (!files.length) {
                        return;
                    }
                    const remaining = this.remainingPhotoSlots();
                    if (remaining <= 0) {
                        this.stepError = `写真は最大${this.maxPhotos}枚までです。既存の写真を削除してから追加してください。`;
                        return;
                    }
                    const selected = files.slice(0, remaining);
                    if (files.length > remaining) {
                        this.stepError = `写真は最大${this.maxPhotos}枚までです。先頭${selected.length}枚を登録します。`;
                    }
                    if (!selected.length) {
                        return;
                    }
                    this.setPhotoFiles([...this.photoFiles, ...selected]);
                },

                handlePhotoDrop(event) {
                    const files = Array.from(event.dataTransfer?.files || []);
                    if (files.length) {
                        this.handlePhotoChange({ target: { files } });
                    }
                },

                setPhotoFiles(files) {
                    const limit = Math.max(this.maxPhotos - this.existingPhotos.length, 0);
                    this.photoFiles = files.slice(0, limit);
                    this.updatePhotoInput();
                    this.refreshPhotoPreviews();
                    if (this.photoFiles.length) {
                        this.stepError = '';
                    }
                },

                updatePhotoInput() {
                    if (!this.$refs.photoInput) {
                        return;
                    }
                    const dataTransfer = new DataTransfer();
                    this.photoFiles.forEach(file => dataTransfer.items.add(file));
                    this.$refs.photoInput.files = dataTransfer.files;
                },

                refreshPhotoPreviews() {
                    this.photoPreviews.forEach(url => URL.revokeObjectURL(url));
                    this.photoPreviews = this.photoFiles.map(file => URL.createObjectURL(file));
                },

                removePhoto(index) {
                    const files = [...this.photoFiles];
                    if (index >= 0 && index < files.length) {
                        files.splice(index, 1);
                        this.setPhotoFiles(files);
                        if (this.hasAnyPhotos()) {
                            this.stepError = '';
                        }
                    }
                },

                removeExistingPhoto(index) {
                    if (!this.isEditing) {
                        return;
                    }
                    if (index >= 0 && index < this.existingPhotos.length) {
                        this.existingPhotos.splice(index, 1);
                        if (this.hasAnyPhotos()) {
                            this.stepError = '';
                        }
                    }
                },

                selectSingleBody(body) {
                    this.form.body_type = [body];
                    this.form.body_type_name = body;
                },

                selectDisplacementCategory(category) {
                    this.form.displacement_categories = [category];
                    this.stepError = '';
                },

                selectFuelType(value) {
                    this.form.fuel_type = value;
                    if (value !== 'その他') {
                        this.form.fuel_type_other = '';
                    }
                    this.stepError = '';
                },

                selectSharingPlan(period) {
                    if (this.form.sharing_periods[0] === period) {
                        this.form.sharing_periods = [];
                    } else {
                        this.form.sharing_periods = [period];
                    }
                    this.stepError = '';
                },

                updatePrice() {
                    const ten = this.parseSelectNumber(this.form.priceTenThousands);
                    const thou = this.parseSelectNumber(this.form.priceThousands);

                    if (ten === null || thou === null) {
                        this.form.price = '';
                        return;
                    }

                    if (ten === 10 && thou > 0) {
                        this.form.priceThousands = '0';
                    }

                    if (ten === 0 && thou === 0) {
                        this.form.price = '';
                        return;
                    }

                    const total = ten * 10000 + thou * 1000;
                    if (total < 1000 || total > 100000) {
                        this.form.price = '';
                        return;
                    }

                    this.form.price = String(total);
                    this.stepError = '';
                },

                parseSelectNumber(value) {
                    if (value === '' || value === null || value === undefined) {
                        return null;
                    }
                    const numeric = Number(value);
                    return Number.isNaN(numeric) ? null : numeric;
                },

                isPriceValid() {
                    const ten = this.parseSelectNumber(this.form.priceTenThousands);
                    const thou = this.parseSelectNumber(this.form.priceThousands);
                    const price = Number(this.form.price);
                    if (ten === null || thou === null) {
                        return false;
                    }
                    if (!price || price < 1000 || price > 100000) {
                        return false;
                    }
                    return price === ten * 10000 + thou * 1000;
                },

                toggleReviewEditPanel() {
                    this.reviewEditPanel = !this.reviewEditPanel;
                    if (this.reviewEditPanel) {
                        this.stepError = '';
                    }
                },

                stepExists(key) {
                    if (['intro', 'type'].includes(key)) {
                        return true;
                    }
                    const flow = this.form.type === 'bike' ? this.bikeSteps : this.carSteps;
                    return flow.includes(key);
                },

                stepIndexFor(key) {
                    if (key === 'intro') {
                        return 0;
                    }
                    if (key === 'type') {
                        return 1;
                    }
                    const flow = this.form.type === 'bike' ? this.bikeSteps : this.carSteps;
                    const index = flow.indexOf(key);
                    if (index === -1) {
                        return null;
                    }
                    return index + 2;
                },

                jumpToStep(key) {
                    const nextIndex = this.stepIndexFor(key);
                    if (nextIndex === null) {
                        return;
                    }
                    this.reviewEditPanel = false;
                    this.stepError = '';
                    this.step = nextIndex;
                },

                next() {
                    if (!this.validateCurrentStep()) {
                        return;
                    }
                    if (this.form.body_type.length === 1) {
                        this.form.body_type_name = this.form.body_type[0];
                    }
                    this.step = Math.min(this.step + 1, (this.form.type ? this.stepsForType.length + 1 : this.baseSteps.length));
                },

                prev() {
                    this.step = Math.max(this.step - 1, 0);
                    this.stepError = '';
                },

                validateCurrentStep() {
                    switch (this.currentStepKey) {
                        case 'type':
                            if (!this.form.type) {
                                this.stepError = '車両タイプを選択してください。';
                                return false;
                            }
                            return true;
                        case 'region':
                            if (!this.form.prefecture_id || !this.form.city_id) {
                                this.stepError = '受け渡し地域を選択してください。';
                                return false;
                            }
                            return true;
                        case 'car_body':
                        case 'bike_body':
                            if (!this.form.body_type.length) {
                                this.stepError = 'ボディタイプを選択してください。';
                                return false;
                            }
                            return true;
                        case 'car_maker':
                        case 'bike_maker':
                            if (!this.form.maker) {
                                this.stepError = 'メーカーを選択してください。';
                                return false;
                            }
                            if (this.form.maker === 'その他' && !this.form.maker_custom) {
                                this.stepError = 'メーカー名を入力してください。';
                                return false;
                            }
                            return true;
                        case 'car_transmission':
                        case 'bike_transmission':
                            if (!this.form.transmission) {
                                this.stepError = 'ミッションを選択してください。';
                                return false;
                            }
                            return true;
                        case 'car_drive':
                        case 'bike_drive':
                            if (!this.form.drivetrain) {
                                this.stepError = '駆動方式を選択してください。';
                                return false;
                            }
                            return true;
                        case 'car_seats':
                            if (!this.form.seats_choice) {
                                this.stepError = '乗車定員を選択してください。';
                                return false;
                            }
                            return true;
                        case 'bike_displacement':
                            if (!this.form.displacement_categories.length) {
                                this.stepError = '排気量カテゴリを選択してください。';
                                return false;
                            }
                            return true;
                        case 'bike_metrics':
                            if (!this.form.weight) {
                                this.stepError = '車両重量を入力してください。';
                                return false;
                            }
                            if (!this.form.seat_height) {
                                this.stepError = 'シート高を入力してください。';
                                return false;
                            }
                            return true;
                        case 'common_model':
                            if (!this.form.model) {
                                this.stepError = '車種名を入力してください。';
                                return false;
                            }
                            return true;
                        case 'common_fuel':
                            if (!this.form.fuel_type) {
                                this.stepError = '燃料の種類を選択してください。';
                                return false;
                            }
                            if (this.form.fuel_type === 'その他' && !this.form.fuel_type_other) {
                                this.stepError = '燃料の種類を入力してください。';
                                return false;
                            }
                            return true;
                        case 'common_year':
                            if (!this.form.year) {
                                this.stepError = '年式を入力してください。';
                                return false;
                            }
                            return true;
                        case 'common_photos':
                            if (!this.hasAnyPhotos()) {
                                this.stepError = '車両写真を少なくとも1枚選択してください。';
                                return false;
                            }
                            return true;
                        case 'common_sharing':
                            if (!this.form.sharing_periods.length) {
                                this.stepError = 'シェアリングプランを選択してください。';
                                return false;
                            }
                            return true;
                        case 'common_price':
                            if (!this.isPriceValid()) {
                                this.stepError = '料金は1,000円〜100,000円の範囲で選択してください。';
                                return false;
                            }
                            return true;
                        default:
                            return true;
                    }
                },
            }));
        });
    </script>
</x-app-layout>
