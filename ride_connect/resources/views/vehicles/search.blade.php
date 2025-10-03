<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <div class="rc-glow-tag">SEARCH</div>
            <h1 class="text-3xl font-semibold text-slate-900 leading-tight">車両検索</h1>
            <p class="text-sm text-slate-500">条件を組み合わせて、希望の車やバイクを見つけましょう。</p>
        </div>
    </x-slot>

    <style>
        .rc-range {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            background: transparent;
            position: absolute;
            inset: 0;
            pointer-events: none;
        }
        .rc-range:focus {
            outline: none;
        }
        .rc-range::-webkit-slider-thumb {
            -webkit-appearance: none;
            height: 18px;
            width: 18px;
            border-radius: 9999px;
            background: #10b981;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 1px rgba(16, 185, 129, 0.4);
            cursor: pointer;
            pointer-events: auto;
        }
        .rc-range::-moz-range-thumb {
            height: 18px;
            width: 18px;
            border-radius: 9999px;
            background: #10b981;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 1px rgba(16, 185, 129, 0.4);
            cursor: pointer;
            pointer-events: auto;
        }
        .rc-range::-webkit-slider-runnable-track {
            height: 0;
        }
        .rc-range::-moz-range-track {
            height: 0;
        }
    </style>

    <div class="rc-shell">
        <div
            x-data="vehicleSearch({
                    type: '{{ $type }}',
                    filters: {{ json_encode($filters, JSON_UNESCAPED_UNICODE) }},
                    prefectures: {{ $prefectures->toJson() }},
                    carBodyTypes: {{ json_encode($carBodyTypes, JSON_UNESCAPED_UNICODE) }},
                    carMakerGroups: {{ json_encode($carMakerGroups, JSON_UNESCAPED_UNICODE) }},
                    carDrivetrains: {{ json_encode($carDrivetrains, JSON_UNESCAPED_UNICODE) }},
                    bikeBodyTypes: {{ json_encode($bikeBodyTypes, JSON_UNESCAPED_UNICODE) }},
                    bikeDisplacementCategories: {{ json_encode($bikeDisplacementCategories, JSON_UNESCAPED_UNICODE) }},
                    bikeMakerGroups: {{ json_encode($bikeMakerGroups, JSON_UNESCAPED_UNICODE) }},
                    bikeDrivetrains: {{ json_encode($bikeDrivetrains, JSON_UNESCAPED_UNICODE) }},
                    sharingPeriods: {{ json_encode($sharingPeriods, JSON_UNESCAPED_UNICODE) }},
                    missions: {{ json_encode($missions, JSON_UNESCAPED_UNICODE) }}
            })"
            x-init="init()"
            class="grid gap-8 lg:grid-cols-[320px,1fr]"
        >
            <form method="GET" action="{{ route('vehicles.index') }}"
                  class="rc-card p-6 space-y-8">
                    <input type="hidden" name="type" :value="state.type">

                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">車両タイプ</h3>
                        <p class="text-sm text-slate-500 mt-1">検索したいカテゴリを選択してください（未選択の場合は全件表示）。</p>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <button type="button"
                                    class="rounded-2xl border px-4 py-4 text-left transition"
                                    :class="state.type === 'car' ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-white hover:bg-slate-100'"
                                    @click="toggleType('car')">
                                <div class="font-semibold">車</div>
                                <div class="text-xs text-slate-500 mt-1">ボディタイプ・乗車定員などで絞り込み</div>
                            </button>
                            <button type="button"
                                    class="rounded-2xl border px-4 py-4 text-left transition"
                                    :class="state.type === 'bike' ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-white hover:bg-slate-100'"
                                    @click="toggleType('bike')">
                                <div class="font-semibold">バイク</div>
                                <div class="text-xs text-slate-500 mt-1">排気量・シート高などで絞り込み</div>
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-slate-400" x-show="!state.type">現在は車・バイクすべてを表示中です。</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">地域で探す</h3>
                        <p class="text-sm text-slate-500 mt-1">受け取り希望のエリアを指定できます。</p>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="text-sm text-slate-500" for="prefecture">都道府県</label>
                                <select id="prefecture" name="prefecture_id" x-model="state.prefecture_id"
                                        @change="handlePrefectureChange(false)"
                                        class="mt-2 rc-input px-3 py-2">
                                    <option value="">指定なし</option>
                                    <template x-for="prefecture in prefectures" :key="prefecture.id">
                                        <option :value="prefecture.id" x-text="prefecture.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm text-slate-500" for="city">市区町村</label>
                                <select id="city" name="city_id" x-model="state.city_id"
                                        class="mt-2 rc-input px-3 py-2" @change="state.city_id_raw = state.city_id">
                                    <option value="">指定なし</option>
                                    <template x-for="city in cities" :key="city.id">
                                        <option :value="city.id" x-text="city.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <template x-if="!state.type">
                        <div class="rounded-2xl border border-slate-200 bg-white/80 px-4 py-6 text-sm text-slate-500">
                            車またはバイクを選択すると詳細な絞り込み項目が表示されます。
                        </div>
                    </template>

                    <template x-if="state.type === 'car'">
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">ボディタイプ</h3>
                                <p class="text-sm text-slate-500 mt-1">複数選択が可能です。</p>
                                <div class="mt-4 grid grid-cols-1 gap-2">
                                    <template x-for="body in carBodyTypes" :key="body">
                                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                            <input type="checkbox" class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400"
                                                   name="car_body_types[]" :value="body" x-model="state.car_body_types">
                                            <span x-text="body"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">メーカー</h3>
                                <div class="mt-4 space-y-4">
                                    <template x-for="group in carMakerGroups" :key="group.label">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-600" x-text="group.label"></div>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <template x-for="maker in group.options" :key="maker">
                                                    <label class="rounded-full border px-4 py-2 text-xs transition"
                                                           :class="state.makers.includes(maker) ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-200 bg-white hover:bg-slate-100'">
                                                        <input type="checkbox" class="hidden" name="makers[]" :value="maker" x-model="state.makers">
                                                        <span x-text="maker"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm text-slate-500">ミッション</label>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <template x-for="mission in missions" :key="mission">
                                            <button type="button"
                                                    class="rounded-full border px-4 py-2 text-xs transition"
                                                    :class="state.transmission === mission ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-white hover:bg-slate-100'"
                                                    @click="toggleSingleSelection('transmission', mission)">
                                                <span x-text="mission"></span>
                                            </button>
                                        </template>
                                    </div>
                                    <input type="hidden" name="transmission" :value="state.transmission">
                                </div>
                                <div>
                                    <label class="text-sm text-slate-500">駆動方式</label>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <template x-for="drive in carDrivetrains" :key="drive">
                                            <button type="button"
                                                    class="rounded-full border px-4 py-2 text-xs transition"
                                                    :class="state.drivetrain === drive ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-white hover:bg-slate-100'"
                                                    @click="toggleSingleSelection('drivetrain', drive)">
                                                <span x-text="drive"></span>
                                            </button>
                                        </template>
                                    </div>
                                    <input type="hidden" name="drivetrain" :value="state.drivetrain">
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm text-slate-500">乗車定員（最小）</label>
                                    <input type="number" name="seats_min" x-model="state.seats_min" min="1" max="9"
                                           class="mt-2 rc-input px-3 py-2"
                                           placeholder="例）2">
                                </div>
                                <div>
                                    <label class="text-sm text-slate-500">乗車定員（最大）</label>
                                    <input type="number" name="seats_max" x-model="state.seats_max" min="1" max="9"
                                           class="mt-2 rc-input px-3 py-2"
                                           placeholder="例）5">
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="state.type === 'bike'">
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">排気量</h3>
                                <p class="text-sm text-slate-500 mt-1">複数選択が可能です。</p>
                                <div class="mt-4 grid grid-cols-1 gap-2">
                                    <template x-for="category in bikeDisplacementCategories" :key="category">
                                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                            <input type="checkbox" class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400"
                                                   name="displacement_categories[]" :value="category" x-model="state.displacement_categories">
                                            <span x-text="category"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">ボディタイプ</h3>
                                <div class="mt-4 grid grid-cols-1 gap-2">
                                    <template x-for="body in bikeBodyTypes" :key="body">
                                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                            <input type="checkbox" class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400"
                                                   name="bike_body_types[]" :value="body" x-model="state.bike_body_types">
                                            <span x-text="body"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">メーカー</h3>
                                <div class="mt-4 space-y-4">
                                    <template x-for="group in bikeMakerGroups" :key="group.label">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-600" x-text="group.label"></div>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <template x-for="maker in group.options" :key="maker">
                                                    <label class="rounded-full border px-4 py-2 text-xs transition"
                                                           :class="state.makers.includes(maker) ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-white hover:bg-slate-100'">
                                                        <input type="checkbox" class="hidden" name="makers[]" :value="maker" x-model="state.makers">
                                                        <span x-text="maker"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm text-slate-500">ミッション</label>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <template x-for="mission in missions" :key="mission">
                                            <button type="button"
                                                    class="rounded-full border px-4 py-2 text-xs transition"
                                                    :class="state.transmission === mission ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-white hover:bg-slate-100'"
                                                    @click="toggleSingleSelection('transmission', mission)">
                                                <span x-text="mission"></span>
                                            </button>
                                        </template>
                                    </div>
                                    <input type="hidden" name="transmission" :value="state.transmission">
                                </div>
                                <div>
                                    <label class="text-sm text-slate-500">駆動方式</label>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <template x-for="drive in bikeDrivetrains" :key="drive">
                                            <button type="button"
                                                    class="rounded-full border px-4 py-2 text-xs transition"
                                                    :class="state.drivetrain === drive ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-white hover:bg-slate-100'"
                                                    @click="toggleSingleSelection('drivetrain', drive)">
                                                <span x-text="drive"></span>
                                            </button>
                                        </template>
                                    </div>
                                    <input type="hidden" name="drivetrain" :value="state.drivetrain">
                                </div>
                            </div>

                            <div>
                                <label class="text-sm text-slate-500">車両重量 (kg)</label>
                                <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-[1fr_auto_1fr] sm:items-center sm:gap-3">
                                    <input type="number" name="weight_min" x-model="state.weight_min" min="50" max="500" step="10"
                                           class="rc-input px-3 py-2"
                                           placeholder="最小">
                                    <span class="text-center text-slate-500 sm:px-2">〜</span>
                                    <input type="number" name="weight_max" x-model="state.weight_max" min="50" max="500" step="10"
                                           class="rc-input px-3 py-2"
                                           placeholder="最大">
                                </div>
                            </div>

                            <div>
                                <label class="text-sm text-slate-500">シート高 (mm)</label>
                                <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-[1fr_auto_1fr] sm:items-center sm:gap-3">
                                    <input type="number" name="seat_height_min" x-model="state.seat_height_min" min="600" max="1000" step="10"
                                           class="rc-input px-3 py-2"
                                           placeholder="最小">
                                    <span class="text-center text-slate-500 sm:px-2">〜</span>
                                    <input type="number" name="seat_height_max" x-model="state.seat_height_max" min="600" max="1000" step="10"
                                           class="rc-input px-3 py-2"
                                           placeholder="最大">
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="space-y-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-slate-900">年式</span>
                                <span class="text-sm text-slate-500" x-text="yearDisplay"></span>
                            </div>
                            <div class="relative h-8">
                                <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 h-1 rounded-full bg-slate-200 z-10"></div>
                                <div class="absolute top-1/2 -translate-y-1/2 h-1 rounded-full bg-emerald-400 z-20" :style="yearTrackStyle"></div>
                                <input type="range" :min="bounds.year.min" :max="bounds.year.max" step="1"
                                       :class="sliderClass('year', 'min')"
                                       x-model.number="state.year_min_slider"
                                       @pointerdown="activateSlider('year', 'min')"
                                       @touchstart="activateSlider('year', 'min')"
                                       @mousedown="activateSlider('year', 'min')"
                                       @input="onYearSliderInput('min')">
                                <input type="range" :min="bounds.year.min" :max="bounds.year.max" step="1"
                                       :class="sliderClass('year', 'max')"
                                       x-model.number="state.year_max_slider"
                                       @pointerdown="activateSlider('year', 'max')"
                                       @touchstart="activateSlider('year', 'max')"
                                       @mousedown="activateSlider('year', 'max')"
                                       @input="onYearSliderInput('max')">
                            </div>
                            <div class="flex justify-between text-xs text-slate-500">
                                <span x-text="bounds.year.min + '年'"></span>
                                <span x-text="bounds.year.max + '年'"></span>
                            </div>
                            <input type="hidden" name="year_min" :value="state.year_min">
                            <input type="hidden" name="year_max" :value="state.year_max">
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-slate-900">料金 (1日)</span>
                                <span class="text-sm text-slate-500" x-text="priceDisplay"></span>
                            </div>
                            <div class="relative h-8">
                                <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 h-1 rounded-full bg-slate-200 z-10"></div>
                                <div class="absolute top-1/2 -translate-y-1/2 h-1 rounded-full bg-emerald-400 z-20" :style="priceTrackStyle"></div>
                                <input type="range" :min="bounds.price.min" :max="bounds.price.max" :step="bounds.price.step"
                                       :class="sliderClass('price', 'min')"
                                       x-model.number="state.price_min_slider"
                                       @pointerdown="activateSlider('price', 'min')"
                                       @touchstart="activateSlider('price', 'min')"
                                       @mousedown="activateSlider('price', 'min')"
                                       @input="onPriceSliderInput('min')">
                                <input type="range" :min="bounds.price.min" :max="bounds.price.max" :step="bounds.price.step"
                                       :class="sliderClass('price', 'max')"
                                       x-model.number="state.price_max_slider"
                                       @pointerdown="activateSlider('price', 'max')"
                                       @touchstart="activateSlider('price', 'max')"
                                       @mousedown="activateSlider('price', 'max')"
                                       @input="onPriceSliderInput('max')">
                            </div>
                            <div class="flex justify-between text-xs text-slate-500">
                                <span x-text="'¥' + bounds.price.min.toLocaleString()"></span>
                                <span x-text="'¥' + bounds.price.max.toLocaleString()"></span>
                            </div>
                            <input type="hidden" name="price_min" :value="state.price_min">
                            <input type="hidden" name="price_max" :value="state.price_max">
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">シェアリング期間</h3>
                            <p class="text-sm text-slate-500 mt-1">受付可能な期間を選択するとマッチしやすくなります。</p>
                            <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                                <template x-for="period in sharingPeriods" :key="period">
                                    <label class="rounded-full border px-4 py-2 text-xs transition"
                                           :class="state.sharing_periods.includes(period) ? 'border-emerald-400 bg-emerald-50 text-emerald-900 shadow-inner' : 'border-slate-300 bg-white hover:bg-slate-100'">
                                        <input type="checkbox" class="hidden" name="sharing_periods[]" :value="period" x-model="state.sharing_periods">
                                        <span x-text="period"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 pt-2">
                        <button type="button"
                                class="text-sm text-slate-500 underline underline-offset-4"
                                @click="resetFilters">
                            条件をリセット
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-6 py-3 font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-600">
                            <span>この条件で検索</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m19 19-4-4m2.5-3.5a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
                            </svg>
                        </button>
                    </div>
                </form>

                <div class="space-y-6">
                    <div class="flex flex-col gap-4 rounded-3xl border border-slate-300 bg-white p-6 shadow-xl">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-sm font-semibold text-emerald-600">検索結果</div>
                                @php
                                    $typeLabel = $type === 'car' ? '車' : ($type === 'bike' ? 'バイク' : '車・バイク');
                                @endphp
                                <div class="text-2xl font-bold text-slate-900">{{ $vehicles->total() }} 件の{{ $typeLabel }}</div>
                            </div>
                            <div class="text-sm text-slate-500">
                                条件を変更するとリアルタイムで更新されます。
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2" x-show="activeChips.length">
                            <template x-for="chip in activeChips" :key="chip">
                                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs text-emerald-700">
                                    <span x-text="chip"></span>
                                </span>
                            </template>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @forelse ($vehicles as $vehicle)
                            <article class="flex flex-col rounded-2xl border border-slate-200 bg-white shadow-lg overflow-hidden">
                                <div class="aspect-[4/3] bg-slate-100 flex items-center justify-center text-slate-500">
                                    <span class="text-xs">写真は準備中です</span>
                                </div>
                                <div class="p-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                                            {{ $vehicle->type === 'car' ? '車' : 'バイク' }}
                                        </span>
                                        <span class="text-xs text-slate-500">{{ $vehicle->year }}年式</span>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-slate-900 leading-snug">
                                            {{ $vehicle->display_maker }} {{ $vehicle->model }}
                                        </h3>
                                        <p class="mt-1 text-xs text-slate-500">{{ optional($vehicle->prefecture)->name }} {{ optional($vehicle->city)->name }}</p>
                                    </div>
                                    <dl class="grid grid-cols-2 gap-2 text-xs text-slate-600">
                                        <div class="rounded-lg bg-white px-2.5 py-2">
                                            <dt class="text-[10px] text-slate-500">料金 (1日)</dt>
                                            <dd class="font-semibold text-slate-900 text-sm">¥{{ number_format($vehicle->price_per_day) }}</dd>
                                        </div>
                                        @if ($vehicle->type === 'car')
                                            <div class="rounded-lg bg-white px-2.5 py-2">
                                                <dt class="text-[10px] text-slate-500">乗車定員</dt>
                                                <dd class="font-semibold text-slate-900 text-sm">{{ $vehicle->display_seats ?? '未設定' }}</dd>
                                            </div>
                                        @else
                                            <div class="rounded-lg bg-white px-2.5 py-2">
                                                <dt class="text-[10px] text-slate-500">車両重量</dt>
                                                <dd class="font-semibold text-slate-900 text-sm">{{ $vehicle->weight ? $vehicle->weight . 'kg' : '未設定' }}</dd>
                                            </div>
                                        @endif
                                        <div class="rounded-lg bg-white px-2.5 py-2 col-span-2">
                                            <dt class="text-[10px] text-slate-500">対応シェア期間</dt>
                                            <dd class="font-semibold text-slate-900 text-sm">
                                                @if (filled($vehicle->sharing_periods))
                                                    {{ implode(' / ', $vehicle->sharing_periods) }}
                                                @else
                                                    未設定
                                                @endif
                                            </dd>
                                        </div>
                                    </dl>
                                    <a href="{{ route('vehicles.show', $vehicle) }}" class="flex w-full items-center justify-center rounded-xl bg-emerald-500 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-emerald-200 transition hover:bg-emerald-600">
                                        詳細を見る
                                    </a>
                                </div>
                            </article>
                        @empty
                            <div class="col-span-full rounded-2xl border border-slate-200 bg-white p-6 text-center text-slate-500 text-sm">
                                条件に一致する車両がまだありません。条件を緩めて再検索してみてください。
                            </div>
                        @endforelse
                    </div>

                    <div>
                        {{ $vehicles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('vehicleSearch', (config) => ({
                prefectures: config.prefectures || [],
                carBodyTypes: config.carBodyTypes || [],
                carMakerGroups: config.carMakerGroups || [],
                carDrivetrains: config.carDrivetrains || [],
                bikeBodyTypes: config.bikeBodyTypes || [],
                bikeDisplacementCategories: config.bikeDisplacementCategories || [],
                bikeMakerGroups: config.bikeMakerGroups || [],
                bikeDrivetrains: config.bikeDrivetrains || [],
                sharingPeriods: config.sharingPeriods || [],
                missions: config.missions || [],
                bounds: {
                    year: { min: 1980, max: 2025 },
                    price: { min: 1000, max: 100000, step: 1000 },
                },
                cities: [],
                sliderActive: { year: 'max', price: 'max' },
                state: {
                    type: ['car', 'bike'].includes(config.type) ? config.type : '',
                    prefecture_id: '',
                    city_id: '',
                    city_id_raw: '',
                    car_body_types: [],
                    bike_body_types: [],
                    displacement_categories: [],
                    makers: [],
                    transmission: '',
                    drivetrain: '',
                    seats_min: '',
                    seats_max: '',
                    weight_min: '',
                    weight_max: '',
                    seat_height_min: '',
                    seat_height_max: '',
                    year_min: '',
                    year_max: '',
                    price_min: '',
                    price_max: '',
                    sharing_periods: [],
                    year_min_slider: 1980,
                    year_max_slider: 2025,
                    price_min_slider: 1000,
                    price_max_slider: 100000,
                },

                init() {
                    this.restoreFilters(config.filters || {});
                    this.initializeSliders();
                    if (this.state.prefecture_id) {
                        this.handlePrefectureChange(true);
                    }
                },

                async handlePrefectureChange(preserveCity = false) {
                    const preserved = preserveCity ? (this.state.city_id_raw || this.state.city_id || '') : '';
                    this.state.city_id = '';
                    if (! preserveCity) {
                        this.state.city_id_raw = '';
                    }
                    this.cities = [];
                    if (! this.state.prefecture_id) {
                        return;
                    }
                    try {
                        const response = await fetch(`/api/prefectures/${this.state.prefecture_id}/cities`);
                        if (response.ok) {
                            this.cities = await response.json();
                            if (preserved) {
                                const match = this.cities.find(city => String(city.id) === String(preserved));
                                if (match) {
                                    this.state.city_id = String(preserved);
                                    this.state.city_id_raw = String(preserved);
                                }
                            }
                        }
                    } catch (error) {
                        console.error('Failed to load cities', error);
                    }
                },

                toggleType(type) {
                    this.state.type = this.state.type === type ? '' : type;
                },

                toggleSingleSelection(field, value) {
                    this.state[field] = this.state[field] === value ? '' : value;
                },

                activateSlider(group, handle) {
                    this.sliderActive[group] = handle;
                },

                sliderClass(group, handle) {
                    return `rc-range ${this.sliderActive[group] === handle ? 'z-40' : 'z-30'}`;
                },

                resetFilters() {
                    const selectedType = this.state.type;
                    this.state = {
                        ...this.state,
                        type: selectedType,
                        prefecture_id: '',
                        city_id: '',
                        city_id_raw: '',
                        car_body_types: [],
                        bike_body_types: [],
                        displacement_categories: [],
                        makers: [],
                        transmission: '',
                        drivetrain: '',
                        seats_min: '',
                        seats_max: '',
                        weight_min: '',
                        weight_max: '',
                        seat_height_min: '',
                        seat_height_max: '',
                        year_min: '',
                        year_max: '',
                        price_min: '',
                        price_max: '',
                        sharing_periods: [],
                        year_min_slider: this.bounds.year.min,
                        year_max_slider: this.bounds.year.max,
                        price_min_slider: this.bounds.price.min,
                        price_max_slider: this.bounds.price.max,
                    };
                    this.cities = [];
                    this.sliderActive = { year: 'max', price: 'max' };
                    this.handlePrefectureChange(false);
                    this.syncYearFilters();
                    this.syncPriceFilters();
                },

                restoreFilters(filters) {
                    const assign = (key, fallback = '') => {
                        if (Array.isArray(filters[key])) {
                            this.state[key] = filters[key].map(String);
                        } else if (filters[key] !== undefined && filters[key] !== null && `${filters[key]}` !== '') {
                            this.state[key] = String(filters[key]);
                        } else {
                            this.state[key] = fallback;
                        }
                    };

                    this.state.type = ['car', 'bike'].includes(filters.type) ? filters.type : '';
                    assign('prefecture_id');
                    this.state.city_id_raw = filters.city_id ? String(filters.city_id) : '';
                    this.state.city_id = this.state.city_id_raw;

                    this.state.car_body_types = this.normalizeArray(filters.car_body_types);
                    this.state.bike_body_types = this.normalizeArray(filters.bike_body_types);
                    this.state.displacement_categories = this.normalizeArray(filters.displacement_categories);
                    this.state.makers = this.normalizeArray(filters.makers);

                    this.state.transmission = filters.transmission ?? '';
                    this.state.drivetrain = filters.drivetrain ?? '';

                    ['seats_min', 'seats_max', 'weight_min', 'weight_max', 'seat_height_min', 'seat_height_max', 'year_min', 'year_max', 'price_min', 'price_max'].forEach(key => assign(key));

                    this.state.sharing_periods = this.normalizeArray(filters.sharing_periods);
                },

                initializeSliders() {
                    this.state.year_min_slider = this.state.year_min ? Number(this.state.year_min) : this.bounds.year.min;
                    this.state.year_max_slider = this.state.year_max ? Number(this.state.year_max) : this.bounds.year.max;
                    this.state.price_min_slider = this.state.price_min ? Number(this.state.price_min) : this.bounds.price.min;
                    this.state.price_max_slider = this.state.price_max ? Number(this.state.price_max) : this.bounds.price.max;
                    this.syncYearFilters();
                    this.syncPriceFilters();
                    this.sliderActive.year = this.state.year_min ? 'min' : 'max';
                    this.sliderActive.price = this.state.price_min ? 'min' : 'max';
                },

                onYearSliderInput(which) {
                    if (which === 'min' && this.state.year_min_slider > this.state.year_max_slider) {
                        this.state.year_max_slider = this.state.year_min_slider;
                    }
                    if (which === 'max' && this.state.year_max_slider < this.state.year_min_slider) {
                        this.state.year_min_slider = this.state.year_max_slider;
                    }
                    this.syncYearFilters();
                },

                syncYearFilters() {
                    const min = Number(this.state.year_min_slider);
                    const max = Number(this.state.year_max_slider);
                    this.state.year_min = min > this.bounds.year.min ? String(min) : '';
                    this.state.year_max = max < this.bounds.year.max ? String(max) : '';
                },

                onPriceSliderInput(which) {
                    if (which === 'min' && this.state.price_min_slider > this.state.price_max_slider) {
                        this.state.price_max_slider = this.state.price_min_slider;
                    }
                    if (which === 'max' && this.state.price_max_slider < this.state.price_min_slider) {
                        this.state.price_min_slider = this.state.price_max_slider;
                    }
                    this.syncPriceFilters();
                },

                syncPriceFilters() {
                    const min = Number(this.state.price_min_slider);
                    const max = Number(this.state.price_max_slider);
                    this.state.price_min = min > this.bounds.price.min ? String(min) : '';
                    this.state.price_max = max < this.bounds.price.max ? String(max) : '';
                },

                normalizeArray(input) {
                    if (Array.isArray(input)) {
                        return input.map(String);
                    }
                    if (typeof input === 'string' && input.length) {
                        try {
                            const decoded = JSON.parse(input);
                            if (Array.isArray(decoded)) {
                                return decoded.map(String);
                            }
                        } catch (error) {
                            return [input];
                        }
                        return [input];
                    }
                    return [];
                },

                get yearDisplay() {
                    const min = this.state.year_min ? Number(this.state.year_min) : this.bounds.year.min;
                    const max = this.state.year_max ? Number(this.state.year_max) : this.bounds.year.max;
                    const fullRange = ! this.state.year_min && ! this.state.year_max;
                    return `${min}年〜${max}年` + (fullRange ? '（全期間）' : '');
                },

                get priceDisplay() {
                    const formatter = (value) => `¥${Number(value).toLocaleString()}`;
                    const min = this.state.price_min ? Number(this.state.price_min) : this.bounds.price.min;
                    const max = this.state.price_max ? Number(this.state.price_max) : this.bounds.price.max;
                    const fullRange = ! this.state.price_min && ! this.state.price_max;
                    return `${formatter(min)}〜${formatter(max)}` + (fullRange ? '（全範囲）' : '');
                },

                get yearTrackStyle() {
                    const range = this.bounds.year.max - this.bounds.year.min;
                    const start = ((this.state.year_min_slider - this.bounds.year.min) / range) * 100;
                    const end = ((this.state.year_max_slider - this.bounds.year.min) / range) * 100;
                    return `left: ${start}%; width: ${Math.max(end - start, 0)}%;`;
                },

                get priceTrackStyle() {
                    const range = this.bounds.price.max - this.bounds.price.min;
                    const start = ((this.state.price_min_slider - this.bounds.price.min) / range) * 100;
                    const end = ((this.state.price_max_slider - this.bounds.price.min) / range) * 100;
                    return `left: ${start}%; width: ${Math.max(end - start, 0)}%;`;
                },

                get activeChips() {
                    const chips = [];
                    if (this.state.type === 'car') {
                        chips.push('カテゴリ: 車');
                    } else if (this.state.type === 'bike') {
                        chips.push('カテゴリ: バイク');
                    }
                    if (this.state.prefecture_id) {
                        const pref = this.prefectures.find(pref => String(pref.id) === String(this.state.prefecture_id));
                        if (pref) chips.push(`地域: ${pref.name}`);
                    }
                    if (this.state.city_id) {
                        const city = this.cities.find(city => String(city.id) === String(this.state.city_id));
                        if (city) chips.push(`市区町村: ${city.name}`);
                    }
                    const pushList = (label, items) => {
                        if (items.length) {
                            chips.push(`${label}: ${items.join(' / ')}`);
                        }
                    };
                    if (this.state.type === 'car') {
                        pushList('ボディタイプ', this.state.car_body_types);
                    } else if (this.state.type === 'bike') {
                        pushList('ボディタイプ', this.state.bike_body_types);
                        pushList('排気量', this.state.displacement_categories);
                    }
                    pushList('メーカー', this.state.makers);
                    if (this.state.transmission) {
                        chips.push(`ミッション: ${this.state.transmission}`);
                    }
                    if (this.state.drivetrain) {
                        chips.push(`駆動方式: ${this.state.drivetrain}`);
                    }
                    if (this.state.seats_min || this.state.seats_max) {
                        chips.push(`乗車定員: ${this.state.seats_min || '指定なし'}〜${this.state.seats_max || '指定なし'}`);
                    }
                    if (this.state.weight_min || this.state.weight_max) {
                        chips.push(`重量: ${this.state.weight_min || '指定なし'}〜${this.state.weight_max || '指定なし'}kg`);
                    }
                    if (this.state.seat_height_min || this.state.seat_height_max) {
                        chips.push(`シート高: ${this.state.seat_height_min || '指定なし'}〜${this.state.seat_height_max || '指定なし'}mm`);
                    }
                    if (this.state.year_min || this.state.year_max) {
                        const min = this.state.year_min || this.bounds.year.min;
                        const max = this.state.year_max || this.bounds.year.max;
                        chips.push(`年式: ${min}年〜${max}年`);
                    }
                    if (this.state.price_min || this.state.price_max) {
                        const formatter = (value) => `¥${Number(value).toLocaleString()}`;
                        const min = this.state.price_min || this.bounds.price.min;
                        const max = this.state.price_max || this.bounds.price.max;
                        chips.push(`料金: ${formatter(min)}〜${formatter(max)}`);
                    }
                    if (this.state.sharing_periods.length) {
                        pushList('シェア期間', this.state.sharing_periods);
                    }
                    return chips;
                },
            }));
        });
    </script>

    @include('partials.bottom-navigation')

</x-app-layout>
