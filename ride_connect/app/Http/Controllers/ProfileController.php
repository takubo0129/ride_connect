<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Prefecture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Support\ProfileMetrics;

class ProfileController extends Controller
{
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

    // プロフィール編集フォーム表示
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->profile;

        $prefectures = Prefecture::orderBy('id')->get(['id', 'name']);

        $prefectureId = null;
        if ($profile && $profile->region) {
            $match = $prefectures->firstWhere('name', $profile->region);
            $prefectureId = $match?->id;
        }

        $cityId = null;
        if ($profile && $profile->city && $prefectureId) {
            $cityId = City::where('prefecture_id', $prefectureId)
                ->where('name', $profile->city)
                ->value('id');
        }

        [$ownedVehicleCars, $ownedVehicleBikes] = $this->normalizeOwnedVehicles($profile);

        $initialProfile = [
            'nickname' => $profile->nickname ?? '',
            'prefecture_id' => $prefectureId,
            'city_id' => $cityId,
            'city' => $profile->city ?? '',
            'gender' => $profile->gender ?? '',
            'age' => $profile->age ?? '',
            'license_type' => $profile->license_type ?? [],
            'license_years_meta' => $profile->license_years_meta ?? [],
            'driving_frequency_car' => $profile->driving_frequency_car ?? '',
            'driving_frequency_bike' => $profile->driving_frequency_bike ?? '',
            'owned_vehicles_car' => $ownedVehicleCars,
            'owned_vehicles_bike' => $ownedVehicleBikes,
            'height' => $profile->height,
            'weight' => $profile->weight,
            'bio' => $profile->bio ?? '',
        ];

        return view('profile.edit', [
            'user' => $user,
            'profile' => $profile,
            'prefectures' => $prefectures,
            'licenseTypes' => self::LICENSE_TYPES,
            'licenseYearOptions' => self::LICENSE_YEAR_OPTIONS,
            'drivingFrequencyOptions' => self::DRIVING_FREQUENCY_OPTIONS,
            'carLicenseTypes' => self::CAR_LICENSE_TYPES,
            'bikeLicenseTypes' => self::BIKE_LICENSE_TYPES,
            'initialProfile' => $initialProfile,
            'heightOptions' => ProfileMetrics::heightOptions(),
            'weightOptions' => ProfileMetrics::weightOptions(),
        ]);
    }

    // プロフィール更新処理
    public function update(Request $request)
    {
        $licenseTypeRule = Rule::in(self::LICENSE_TYPES);
        $licenseYearRule = Rule::in(self::LICENSE_YEAR_OPTIONS);
        $drivingFrequencyRule = Rule::in(array_merge(self::DRIVING_FREQUENCY_OPTIONS, ['免許なし']));

        $validated = $request->validate([
            'nickname' => 'required|string|max:30',
            'prefecture_id' => ['nullable', 'exists:prefectures,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'gender' => ['required', 'string', Rule::in(['男性', '女性', '未回答'])],
            'age' => ['required', 'integer', 'between:18,100'],
            'license_type' => ['required', 'array', 'min:1'],
            'license_type.*' => ['string', $licenseTypeRule],
            'license_years_meta' => ['nullable', 'array'],
            'license_years_meta.*' => ['nullable', 'string', $licenseYearRule],
            'driving_frequency_car' => ['nullable', 'string', $drivingFrequencyRule],
            'driving_frequency_bike' => ['nullable', 'string', $drivingFrequencyRule],
            'owned_vehicles_car' => ['nullable', 'array', 'max:20'],
            'owned_vehicles_car.*' => ['nullable', 'string', 'max:100'],
            'owned_vehicles_bike' => ['nullable', 'array', 'max:20'],
            'owned_vehicles_bike.*' => ['nullable', 'string', 'max:100'],
            'height' => ['nullable', 'integer', Rule::in(ProfileMetrics::heightOptionValues())],
            'weight' => ['nullable', 'integer', Rule::in(ProfileMetrics::weightOptionValues())],
            'bio' => 'nullable|string|max:500',
        ]);

        $licenses = collect($validated['license_type'])
            ->filter(fn ($license) => filled($license))
            ->values()
            ->all();

        if (empty($licenses)) {
            return back()->withErrors(['license_type' => '所持免許を少なくとも1つ選択してください。'])->withInput();
        }

        if (in_array('免許なし', $licenses, true) && count($licenses) > 1) {
            return back()->withErrors(['license_type' => '「免許なし」は他の免許と同時に選択できません。'])->withInput();
        }

        $licenseYearsMeta = collect($validated['license_years_meta'] ?? [])
            ->only($licenses)
            ->filter(fn ($value) => filled($value) && $value !== '選択してください')
            ->toArray();

        $drivingFrequencyCar = $validated['driving_frequency_car'] ?? '';
        $drivingFrequencyBike = $validated['driving_frequency_bike'] ?? '';

        $requiresCarFrequency = collect($licenses)->intersect(self::CAR_LICENSE_TYPES)->isNotEmpty();
        $requiresBikeFrequency = collect($licenses)->intersect(self::BIKE_LICENSE_TYPES)->isNotEmpty();

        if (in_array('免許なし', $licenses, true)) {
            $licenses = ['免許なし'];
            $licenseYearsMeta = [];
            $drivingFrequencyCar = '免許なし';
            $drivingFrequencyBike = '免許なし';
        } else {
            if ($requiresCarFrequency && $drivingFrequencyCar === '') {
                return back()->withErrors(['driving_frequency_car' => '車の運転頻度を選択してください。'])->withInput();
            }

            if ($requiresBikeFrequency && $drivingFrequencyBike === '') {
                return back()->withErrors(['driving_frequency_bike' => 'バイクの運転頻度を選択してください。'])->withInput();
            }

            if (! $requiresCarFrequency) {
                $drivingFrequencyCar = null;
            } elseif ($drivingFrequencyCar === '免許なし') {
                $drivingFrequencyCar = '';
            }

            if (! $requiresBikeFrequency) {
                $drivingFrequencyBike = null;
            } elseif ($drivingFrequencyBike === '免許なし') {
                $drivingFrequencyBike = '';
            }
        }

        $prefectureName = null;
        if (! empty($validated['prefecture_id'])) {
            $prefectureName = Prefecture::where('id', $validated['prefecture_id'])->value('name');
        }

        $cityName = null;
        if (! empty($validated['city_id'])) {
            $city = City::where('id', $validated['city_id'])
                ->when($validated['prefecture_id'] ?? null, fn ($query, $prefectureId) => $query->where('prefecture_id', $prefectureId))
                ->first();

            if (! $city) {
                return back()->withErrors(['city_id' => '選択した市区町村が都道府県と一致しません。'])->withInput();
            }

            $cityName = $city->name;
        }

        $profile = Auth::user()->profile;

        $regionValue = $prefectureName ?? $profile->region ?? '';
        $cityValue = $cityName ?? (($validated['prefecture_id'] ?? null) ? null : $profile->city);

        $ownedVehicleCars = collect($validated['owned_vehicles_car'] ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->take(20);

        $ownedVehicleBikes = collect($validated['owned_vehicles_bike'] ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->take(20);

        $ownedVehicleSummary = $ownedVehicleCars
            ->merge($ownedVehicleBikes)
            ->implode(' / ');

        $heightValue = array_key_exists('height', $validated) && $validated['height'] !== null
            ? (int) $validated['height']
            : null;

        $weightValue = array_key_exists('weight', $validated) && $validated['weight'] !== null
            ? (int) $validated['weight']
            : null;

        $profile->update([
            'nickname' => $validated['nickname'],
            'region' => $regionValue,
            'city' => $cityValue,
            'gender' => $validated['gender'],
            'age' => (int) $validated['age'],
            'license_type' => $licenses,
            'license_years_meta' => $licenseYearsMeta,
            'driving_frequency_car' => $drivingFrequencyCar,
            'driving_frequency_bike' => $drivingFrequencyBike,
            'owned_vehicle' => $ownedVehicleSummary ?: null,
            'owned_vehicles' => [
                'cars' => $ownedVehicleCars->all(),
                'bikes' => $ownedVehicleBikes->all(),
            ],
            'height' => $heightValue,
            'weight' => $weightValue,
            'bio' => $validated['bio'] ?? null,
        ]);

        return redirect()->route('profile.edit')->with('success', 'プロフィールを更新しました！');
    }

    // アカウント削除処理
    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
}

    /**
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function normalizeOwnedVehicles($profile): array
    {
        $cars = [];
        $bikes = [];

        $raw = $profile->owned_vehicles;

        if (is_array($raw) && $raw !== []) {
            if (array_is_list($raw)) {
                foreach ($raw as $vehicle) {
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
                        $cars[] = $label;
                    } elseif ($type === 'bike') {
                        $bikes[] = $label;
                    }
                }
            } else {
                $cars = collect($raw['cars'] ?? [])
                    ->map(fn ($value) => trim((string) $value))
                    ->filter(fn ($value) => $value !== '')
                    ->values()
                    ->all();

                $bikes = collect($raw['bikes'] ?? [])
                    ->map(fn ($value) => trim((string) $value))
                    ->filter(fn ($value) => $value !== '')
                    ->values()
                    ->all();
            }
        }

        if ($cars === [] && $bikes === [] && filled($profile->owned_vehicle)) {
            $fallback = collect(explode('/', (string) $profile->owned_vehicle))
                ->map(fn ($value) => trim($value))
                ->filter(fn ($value) => $value !== '')
                ->take(20)
                ->values()
                ->all();

            if ($fallback !== []) {
                $cars = $fallback;
            }
        }

        return [$cars, $bikes];
    }
}
