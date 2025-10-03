<?php

namespace App\Http\Controllers;

use App\Models\Prefecture;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Support\ProfileMetrics;

class OnboardingController extends Controller
{
    private const LICENSE_YEARS_PARAM = 'license_years_meta';

    private const LICENSE_TYPES = [
        '原付免許',
        '小型二輪免許',
        '小型二輪免許（AT限定）',
        '普通自動二輪免許',
        '普通自動二輪免許（AT限定）',
        '大型二輪免許',
        '大型二輪免許（AT限定）',
        '普通免許',
        '普通免許（AT限定）',
        '免許なし',
    ];

    private const LICENSE_YEAR_OPTIONS = [
        '1年未満',
        '1年以上',
        '2年以上',
        '3年以上',
        '4年以上',
        '5年以上',
        '免許なし',
    ];

    private const DRIVING_FREQUENCY_OPTIONS = [
        '毎日',
        '3日に1回',
        '1週間に1回',
        '半月に1回',
        '1ヶ月に1回',
        '年に数回',
    ];

    private const CAR_LICENSE_TYPES = [
        '普通免許',
        '普通免許（AT限定）',
    ];

    private const BIKE_LICENSE_TYPES = [
        '原付免許',
        '小型二輪免許',
        '小型二輪免許（AT限定）',
        '普通自動二輪免許',
        '普通自動二輪免許（AT限定）',
        '大型二輪免許',
        '大型二輪免許（AT限定）',
    ];

    private const CAR_MAKERS = [
        '国産車' => [
            'トヨタ',
            '日産',
            'ホンダ',
            'マツダ',
            'スズキ',
            'スバル',
            '三菱',
            'レクサス',
            'ダイハツ',
            'その他',
        ],
        '外国車' => [
            'Aston Martin（アストンマーティン）',
            'Alfa Romeo（アルファロメオ）',
            'Alpine（アルピーヌ）',
            'Audi（アウディ）',
            'BMW（ビーエムダブリュー）',
            'Cadillac（キャデラック）',
            'Chrysler Jeep（クライスラー・ジープ）',
            'GMC（ジーエムシー）',
            'Chevrolet（シボレー）',
            'Jaguar（ジャガー）',
            'Tesla（テスラ）',
            'Dodge（ダッジ）',
            'Fiat（フィアット）',
            'Ford（フォード）',
            'Volkswagen（フォルクスワーゲン）',
            'Peugeot（プジョー）',
            'Volvo（ボルボ）',
            'Porsche（ポルシェ）',
            'Bentley（ベントレー）',
            'MINI（ミニ）',
            'Mercedes-Benz（メルセデス・ベンツ）',
            'Maserati（マセラティ）',
            'Land Rover（ランドローバー）',
            'Renault（ルノー）',
            'Lotus（ロータス）',
            'Hummer（ハマー）',
            'Hyundai（ヒョンデ）',
            'その他',
        ],
    ];

    private const BIKE_MAKERS = [
        '国産車' => [
            'ホンダ',
            'ヤマハ',
            'スズキ',
            'カワサキ',
            'メグロ',
            'その他',
        ],
        '外国車' => [
            'Aprilia（アプリリア）',
            'BMW（ビーエムダブリュー）',
            'Ducati（ドゥカティ）',
            'Triumph（トライアンフ）',
            'Husqvarna（ハスクバーナ）',
            'Harley-Davidson（ハーレーダビッドソン）',
            'Bimota（ビモータ）',
            'Moto Guzzi（モトグッツィ）',
            'MV Agusta（エムブイ アグスタ）',
            'Royal Enfield（ロイヤルエンフィールド）',
            'KTM（ケーティーエム）',
            'その他',
        ],
    ];

    public function landing(): View
    {
        return view('onboarding.landing', [
            'redirectUrl' => Auth::check()
                ? route('home')
                : route('login'),
        ]);
    }

    public function showProfileForm(): RedirectResponse|View
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $profile = $user->profile;

        if ($profile && $profile->onboarding_completed) {
            return redirect()->route('home');
        }

        $prefectures = Prefecture::orderBy('id')->get(['id', 'name']);

        return view('onboarding.profile', [
            'prefectures' => $prefectures,
            'licenseTypes' => self::LICENSE_TYPES,
            'licenseYearOptions' => self::LICENSE_YEAR_OPTIONS,
            'drivingFrequencyOptions' => self::DRIVING_FREQUENCY_OPTIONS,
            'carLicenseTypes' => self::CAR_LICENSE_TYPES,
            'bikeLicenseTypes' => self::BIKE_LICENSE_TYPES,
            'carMakerGroups' => collect(self::CAR_MAKERS)->map(fn ($options, $label) => [
                'label' => $label,
                'options' => $options,
            ])->values()->all(),
            'bikeMakerGroups' => collect(self::BIKE_MAKERS)->map(fn ($options, $label) => [
                'label' => $label,
                'options' => $options,
            ])->values()->all(),
            'profile' => $profile,
            'heightOptions' => ProfileMetrics::heightOptions(),
            'weightOptions' => ProfileMetrics::weightOptions(),
        ]);
    }

    public function storeProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $profile = $user->profile;

        $ownedVehiclesInput = json_decode($request->input('owned_vehicles', '[]'), true);
        if (! is_array($ownedVehiclesInput)) {
            $ownedVehiclesInput = [];
        }

        $ownsNone = $request->boolean('owns_none');
        $normalizedOwnedVehicles = [];

        // Snapshots for debugging in case validation fails before normalization.
        $request->attributes->set(self::LICENSE_YEARS_PARAM . '_raw', $request->input(self::LICENSE_YEARS_PARAM));
        $request->attributes->set(self::LICENSE_YEARS_PARAM . '_json_raw', $request->input(self::LICENSE_YEARS_PARAM . '_json'));

        $licenseYears = $this->normalizeLicenseYears($request);
        $request->merge([self::LICENSE_YEARS_PARAM => $licenseYears]);

        $carMakers = collect(self::CAR_MAKERS)->flatten()->unique()->values()->all();
        $bikeMakers = collect(self::BIKE_MAKERS)->flatten()->unique()->values()->all();

        $validator = Validator::make($request->all(), [
            'nickname' => ['required', 'string', 'max:30'],
            'region' => ['required', 'string', 'max:50'],
            'city' => ['required', 'string', 'max:50'],
            'gender' => ['required', 'string', 'in:男性,女性,未回答'],
            'age' => ['required', 'integer', 'between:18,100'],
            'license_type' => ['required', 'array', 'min:1'],
            'license_type.*' => ['string', 'in:' . implode(',', self::LICENSE_TYPES)],
            self::LICENSE_YEARS_PARAM => ['nullable', 'array'],
            self::LICENSE_YEARS_PARAM . '.*' => ['nullable', 'string', 'in:' . implode(',', self::LICENSE_YEAR_OPTIONS)],
            'driving_frequency_car' => ['nullable', 'string', 'in:' . implode(',', array_merge(self::DRIVING_FREQUENCY_OPTIONS, ['免許なし']))],
            'driving_frequency_bike' => ['nullable', 'string', 'in:' . implode(',', array_merge(self::DRIVING_FREQUENCY_OPTIONS, ['免許なし']))],
            'owned_vehicle' => ['nullable', 'string', 'max:100'],
            'height' => ['nullable', 'integer', Rule::in(ProfileMetrics::heightOptionValues())],
            'weight' => ['nullable', 'integer', Rule::in(ProfileMetrics::weightOptionValues())],
            'bio' => ['nullable', 'string', 'max:200'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $licenses = $request->input('license_type', []);

            if (in_array('免許なし', $licenses, true)) {
                if (count($licenses) > 1) {
                    $validator->errors()->add('license_type', '「免許なし」と他の免許は同時に選択できません。');
                }

                return;
            }

            $years = $request->input(self::LICENSE_YEARS_PARAM, []);

            foreach ($licenses as $license) {
                if (! array_key_exists($license, $years) || empty($years[$license])) {
                    Log::info('Onboarding missing license years', [
                        'user_id' => optional(Auth::user())->id,
                        'license' => $license,
                        'licenses' => $licenses,
                        'normalized_years' => $years,
                        'raw_years' => $request->attributes->get(self::LICENSE_YEARS_PARAM . '_raw'),
                        'raw_json' => $request->attributes->get(self::LICENSE_YEARS_PARAM . '_json_raw'),
                    ]);

                    $validator->errors()->add('license_years_meta.' . $license, '免許ごとの取得年数を選択してください。');
                }
            }
        });

        $validator->after(function ($validator) use ($ownedVehiclesInput, $ownsNone, $carMakers, $bikeMakers, &$normalizedOwnedVehicles) {
            if ($ownsNone) {
                $normalizedOwnedVehicles = [];
                return;
            }

            foreach ($ownedVehiclesInput as $index => $vehicle) {
                if (! is_array($vehicle)) {
                    $validator->errors()->add('owned_vehicles', '所有している車・バイクの入力形式が正しくありません。');
                    continue;
                }

                $type = $vehicle['type'] ?? null;
                $maker = trim((string) ($vehicle['maker'] ?? ''));
                $makerCustom = trim((string) ($vehicle['maker_custom'] ?? ''));
                $model = trim((string) ($vehicle['model'] ?? ''));

                if (! in_array($type, ['car', 'bike'], true)) {
                    $validator->errors()->add('owned_vehicles.' . $index . '.type', '車種区分を選択してください。');
                    continue;
                }

                $allowedMakers = $type === 'car' ? $carMakers : $bikeMakers;

                if ($maker === '') {
                    $validator->errors()->add('owned_vehicles.' . $index . '.maker', 'メーカーを選択してください。');
                    continue;
                }

                if (! in_array($maker, $allowedMakers, true)) {
                    $validator->errors()->add('owned_vehicles.' . $index . '.maker', '選択できないメーカーが含まれています。');
                    continue;
                }

                if ($maker === 'その他' && $makerCustom === '') {
                    $validator->errors()->add('owned_vehicles.' . $index . '.maker_custom', 'その他のメーカー名を入力してください。');
                    continue;
                }

                if (mb_strlen($makerCustom) > 100) {
                    $validator->errors()->add('owned_vehicles.' . $index . '.maker_custom', 'メーカー名は100文字以内で入力してください。');
                    continue;
                }

                if ($model === '') {
                    $validator->errors()->add('owned_vehicles.' . $index . '.model', '車種名を入力してください。');
                    continue;
                }

                if (mb_strlen($model) > 100) {
                    $validator->errors()->add('owned_vehicles.' . $index . '.model', '車種名は100文字以内で入力してください。');
                    continue;
                }

                $normalizedOwnedVehicles[] = [
                    'type' => $type,
                    'maker' => $maker,
                    'maker_custom' => $maker === 'その他' ? $makerCustom : null,
                    'model' => $model,
                ];
            }
        });

        $validator->validate();

        $data = $validator->validated();

        $hasNoLicense = in_array('免許なし', $data['license_type'], true);

        if ($hasNoLicense) {
            $data[self::LICENSE_YEARS_PARAM] = ['免許なし' => '免許なし'];
            $data['driving_frequency_car'] = '免許なし';
            $data['driving_frequency_bike'] = '免許なし';
        } else {
            $requiresCarFrequency = collect($data['license_type'])->intersect(self::CAR_LICENSE_TYPES)->isNotEmpty();
            $requiresBikeFrequency = collect($data['license_type'])->intersect(self::BIKE_LICENSE_TYPES)->isNotEmpty();

            if ($requiresCarFrequency && empty($data['driving_frequency_car'])) {
                $validator->errors()->add('driving_frequency_car', '車の運転頻度を選択してください。');
            }

            if ($requiresBikeFrequency && empty($data['driving_frequency_bike'])) {
                $validator->errors()->add('driving_frequency_bike', 'バイクの運転頻度を選択してください。');
            }

            if ($validator->errors()->isNotEmpty()) {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }

            $data['driving_frequency_car'] = $requiresCarFrequency
                ? ($data['driving_frequency_car'] === '免許なし' ? '' : $data['driving_frequency_car'])
                : null;

            $data['driving_frequency_bike'] = $requiresBikeFrequency
                ? ($data['driving_frequency_bike'] === '免許なし' ? '' : $data['driving_frequency_bike'])
                : null;
        }

        $cleanYears = [];
        foreach ($data['license_type'] as $license) {
            if (isset($data['license_years_meta'][$license])) {
                $cleanYears[$license] = $data['license_years_meta'][$license];
            }
        }

        if ($hasNoLicense) {
            $cleanYears = ['免許なし' => '免許なし'];
        }

        $profile->update([
            'nickname' => $data['nickname'],
            'region' => $data['region'],
            'city' => $data['city'],
            'gender' => $data['gender'],
            'age' => (int) $data['age'],
            'license_type' => $data['license_type'],
            'license_years_meta' => $cleanYears,
            'driving_frequency' => null,
            'driving_frequency_car' => $data['driving_frequency_car'],
            'driving_frequency_bike' => $data['driving_frequency_bike'],
            'owned_vehicle' => $data['owned_vehicle'] ?? '',
            'owned_vehicles' => $ownsNone ? [] : $normalizedOwnedVehicles,
            'height' => $data['height'] ?? null,
            'weight' => $data['weight'] ?? null,
            'bio' => $data['bio'] ?? '',
            'license_years' => null,
            'onboarding_completed' => true,
        ]);

        return redirect()->route('onboarding.profile.complete');
    }

    private function normalizeLicenseYears(Request $request): array
    {
        $licenseYears = [];

        $licenseYearsJson = $request->input(self::LICENSE_YEARS_PARAM . '_json');
        if (is_string($licenseYearsJson) && $licenseYearsJson !== '') {
            $decoded = json_decode($licenseYearsJson, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $licenseYears = $decoded;
            }
        }

        $arrayInput = $request->input(self::LICENSE_YEARS_PARAM);
        if (is_array($arrayInput)) {
            foreach ($arrayInput as $license => $value) {
                if (is_array($value)) {
                    $value = reset($value);
                }

                $value = is_scalar($value) ? trim((string) $value) : '';
                $licenseKey = is_string($license) ? trim($license) : (string) $license;

                if ($value === '') {
                    continue;
                }

                $licenseYears[$licenseKey] = $value;
            }
        }

        $normalized = collect($licenseYears)
            ->mapWithKeys(function ($value, $key) {
                if (is_array($value)) {
                    $value = reset($value);
                }

                $value = is_scalar($value) ? trim((string) $value) : '';
                $key = is_string($key) ? trim($key) : (string) $key;

                return [$key => $value];
            })
            ->filter(fn ($value) => filled($value))
            ->toArray();

        Log::debug('normalize_license_years', [
            'user_id' => optional(Auth::user())->id,
            'license_type' => $request->input('license_type', []),
            'raw_json' => $request->attributes->get(self::LICENSE_YEARS_PARAM . '_json_raw'),
            'raw_array' => $request->attributes->get(self::LICENSE_YEARS_PARAM . '_raw'),
            'normalized' => $normalized,
        ]);

        if (in_array('免許なし', (array) $request->input('license_type', []), true)) {
            return ['免許なし' => '免許なし'];
        }

        unset($normalized['免許なし']);

        return $normalized;
    }

    public function complete(): RedirectResponse|View
    {
        $user = Auth::user();

        if (! $user || ! $user->profile || ! $user->profile->onboarding_completed) {
            return redirect()->route('onboarding.profile');
        }

        return view('onboarding.complete');
    }
}
