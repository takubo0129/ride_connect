<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Prefecture;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VehicleController extends Controller
{
    private const SHARING_PERIODS = [
        '1day', '2day', '3day', '4day', '5day', '6day',
        '1week', '2week', '3week', '4week', '1month',
    ];

    private const CAR_BODY_TYPES = [
        '軽自動車', 'SUV', 'ミニバン', 'ハイブリッド', 'EV', 'PHEV', 'ワンボックス',
        'コンパクトカー', 'セダン', 'クーペ', 'ステーションワゴン', 'オープンカー', 'キャンピングカー',
    ];

    private const CAR_MAKERS = [
        '国産車' => ['トヨタ', '日産', 'ホンダ', 'マツダ', 'スズキ', 'スバル', '三菱', 'レクサス', 'ダイハツ', 'その他'],
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

    private const CAR_TRANSMISSIONS = ['AT', 'MT'];
    private const CAR_DRIVETRAINS = ['2WD', '4WD'];
    private const CAR_SEAT_OPTIONS = ['1', '2', '3', '4', '5', '6', '7', '8', '9+'];

    private const BIKE_DISPLACEMENT_CATEGORIES = [
        '50cc（原付）',
        '51cc〜125cc（小型自動二輪）',
        '126cc〜250cc（普通自動二輪）',
        '251cc〜400cc（普通自動二輪）',
        '401cc〜750cc（大型自動二輪）',
        '751cc以上（大型自動二輪）',
    ];

    private const BIKE_BODY_TYPES = [
        'アメリカン', 'ストリート', 'オールドルック', 'ミニバイク', 'ネイキッド',
        'スポーツ/レプリカ', 'オフロード', 'スクーター', 'ツアラー', 'アドベンチャー',
        'スクランブラー', 'トライク',
    ];

    private const BIKE_MAKERS = [
        '国産車' => ['ホンダ', 'ヤマハ', 'スズキ', 'カワサキ', 'メグロ', 'その他'],
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

    private const BIKE_TRANSMISSIONS = ['AT', 'MT'];
    private const BIKE_DRIVETRAINS = ['チェーン', 'ベルト', 'シャフト'];

    public function index(Request $request)
    {
        $type = $request->input('type');
        if (! in_array($type, ['car', 'bike'], true)) {
            $type = null;
        }

        $query = Vehicle::query()
            ->with(['prefecture', 'city']);

        if ($type) {
            $query->where('type', $type);
        }

        if ($prefectureId = $request->input('prefecture_id')) {
            $query->where('prefecture_id', $prefectureId);
        }

        if ($cityId = $request->input('city_id')) {
            $query->where('city_id', $cityId);
        }

        if ($yearMin = $request->integer('year_min')) {
            $query->where('year', '>=', $yearMin);
        }

        if ($yearMax = $request->integer('year_max')) {
            $query->where('year', '<=', $yearMax);
        }

        if ($priceMin = $request->integer('price_min')) {
            $query->where('price_per_day', '>=', $priceMin);
        }

        if ($priceMax = $request->integer('price_max')) {
            $query->where('price_per_day', '<=', $priceMax);
        }

        if ($sharing = $this->sanitizeArray($request->input('sharing_periods'))) {
            $query->where(function ($q) use ($sharing) {
                foreach ($sharing as $period) {
                    $q->orWhereJsonContains('sharing_periods', $period);
                }
            });
        }

        if ($transmission = $request->input('transmission')) {
            $query->where('transmission', $transmission);
        }

        if ($drivetrain = $request->input('drivetrain')) {
            $query->where('drivetrain', $drivetrain);
        }

        if ($type === 'car') {
            if ($bodyTypes = $this->sanitizeArray($request->input('car_body_types'))) {
                foreach ($bodyTypes as $bodyType) {
                    $query->whereJsonContains('body_type', $bodyType);
                }
            }

            if ($makers = $this->sanitizeArray($request->input('makers'))) {
                $query->where(function ($makerQuery) use ($makers) {
                    $makerQuery->whereIn('maker', array_diff($makers, ['その他']));
                    if (in_array('その他', $makers, true)) {
                        $makerQuery->orWhere('maker', 'その他');
                    }
                });
            }

            if ($seatsMin = $request->integer('seats_min')) {
                $query->where('seats', '>=', $seatsMin);
            }

            if ($seatsMax = $request->integer('seats_max')) {
                $query->where('seats', '<=', $seatsMax);
            }
        } elseif ($type === 'bike') {
            if ($displacements = $this->sanitizeArray($request->input('displacement_categories'))) {
                foreach ($displacements as $category) {
                    $query->whereJsonContains('displacement_categories', $category);
                }
            }

            if ($bodyTypes = $this->sanitizeArray($request->input('bike_body_types'))) {
                foreach ($bodyTypes as $bodyType) {
                    $query->whereJsonContains('body_type', $bodyType);
                }
            }

            if ($makers = $this->sanitizeArray($request->input('makers'))) {
                $query->where(function ($makerQuery) use ($makers) {
                    $makerQuery->whereIn('maker', array_diff($makers, ['その他']));
                    if (in_array('その他', $makers, true)) {
                        $makerQuery->orWhere('maker', 'その他');
                    }
                });
            }

            if ($weightMin = $request->integer('weight_min')) {
                $query->where('weight', '>=', $weightMin);
            }

            if ($weightMax = $request->integer('weight_max')) {
                $query->where('weight', '<=', $weightMax);
            }

            if ($seatHeightMin = $request->integer('seat_height_min')) {
                $query->where('seat_height', '>=', $seatHeightMin);
            }

            if ($seatHeightMax = $request->integer('seat_height_max')) {
                $query->where('seat_height', '<=', $seatHeightMax);
            }
        }

        $vehicles = $query->latest()->paginate(12)->withQueryString();

        $prefectures = Prefecture::orderBy('id')->get(['id', 'name']);

        return view('vehicles.search', [
            'prefectures' => $prefectures,
            'vehicles' => $vehicles,
            'filters' => $request->all(),
            'type' => $type,
            'carBodyTypes' => self::CAR_BODY_TYPES,
            'carMakerGroups' => $this->normalizeMakerGroups(self::CAR_MAKERS),
            'bikeBodyTypes' => self::BIKE_BODY_TYPES,
            'bikeDisplacementCategories' => self::BIKE_DISPLACEMENT_CATEGORIES,
            'bikeMakerGroups' => $this->normalizeMakerGroups(self::BIKE_MAKERS),
            'sharingPeriods' => self::SHARING_PERIODS,
            'carDrivetrains' => self::CAR_DRIVETRAINS,
            'bikeDrivetrains' => self::BIKE_DRIVETRAINS,
            'missions' => self::CAR_TRANSMISSIONS,
        ]);
    }

    public function create()
    {
        $prefectures = Prefecture::orderBy('id')->get(['id', 'name']);

        $oldInput = session()->getOldInput();

        $oldValues = $this->buildOldValuesFromArray($oldInput);

        return view('vehicles.create', [
            'prefectures' => $prefectures,
            'carBodyTypes' => self::CAR_BODY_TYPES,
            'carMakerGroups' => $this->normalizeMakerGroups(self::CAR_MAKERS),
            'carDrivetrains' => self::CAR_DRIVETRAINS,
            'carSeatOptions' => self::CAR_SEAT_OPTIONS,
            'bikeDisplacementCategories' => self::BIKE_DISPLACEMENT_CATEGORIES,
            'bikeBodyTypes' => self::BIKE_BODY_TYPES,
            'bikeMakerGroups' => $this->normalizeMakerGroups(self::BIKE_MAKERS),
            'bikeDrivetrains' => self::BIKE_DRIVETRAINS,
            'sharingPeriods' => self::SHARING_PERIODS,
            'missions' => self::CAR_TRANSMISSIONS,
            'oldValues' => $oldValues,
            'editingVehicle' => null,
            'existingImages' => [],
        ]);
    }

    public function edit(Vehicle $vehicle)
    {
        $this->ensureOwnedByCurrentUser($vehicle);

        $prefectures = Prefecture::orderBy('id')->get(['id', 'name']);

        $oldValues = $this->buildOldValuesFromVehicle($vehicle);

        $existingImages = collect($vehicle->images ?? [])
            ->map(fn ($path) => [
                'path' => $path,
                'url' => Storage::url($path),
            ])
            ->values()
            ->all();

        return view('vehicles.create', [
            'prefectures' => $prefectures,
            'carBodyTypes' => self::CAR_BODY_TYPES,
            'carMakerGroups' => $this->normalizeMakerGroups(self::CAR_MAKERS),
            'carDrivetrains' => self::CAR_DRIVETRAINS,
            'carSeatOptions' => self::CAR_SEAT_OPTIONS,
            'bikeDisplacementCategories' => self::BIKE_DISPLACEMENT_CATEGORIES,
            'bikeBodyTypes' => self::BIKE_BODY_TYPES,
            'bikeMakerGroups' => $this->normalizeMakerGroups(self::BIKE_MAKERS),
            'bikeDrivetrains' => self::BIKE_DRIVETRAINS,
            'sharingPeriods' => self::SHARING_PERIODS,
            'missions' => self::CAR_TRANSMISSIONS,
            'oldValues' => $oldValues,
            'editingVehicle' => $vehicle,
            'existingImages' => $existingImages,
        ]);
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['prefecture:id,name', 'city:id,name', 'user:id,name']);
        $user = Auth::user();

        $sharingPeriodMeta = [
            '1day' => ['label' => '1日', 'days' => 1],
            '2day' => ['label' => '2日', 'days' => 2],
            '3day' => ['label' => '3日', 'days' => 3],
            '4day' => ['label' => '4日', 'days' => 4],
            '5day' => ['label' => '5日', 'days' => 5],
            '6day' => ['label' => '6日', 'days' => 6],
            '1week' => ['label' => '1週間', 'days' => 7],
            '2week' => ['label' => '2週間', 'days' => 14],
            '3week' => ['label' => '3週間', 'days' => 21],
            '4week' => ['label' => '4週間', 'days' => 28],
            '1month' => ['label' => '1ヶ月', 'days' => 30],
        ];

        $sharingPeriods = collect($vehicle->sharing_periods ?? [])
            ->filter(fn ($key) => isset($sharingPeriodMeta[$key]))
            ->map(fn ($key) => array_merge(['key' => $key], $sharingPeriodMeta[$key]))
            ->values();

        $cautionLabels = [
            'operation' => '操作に関して',
            'driving' => '走行に関して',
            'capacity' => '乗車定員に関して',
            'return' => '返却に関して',
            'refuel' => '給油に関して',
            'other' => 'その他の注意事項',
        ];

        $cautionSections = collect($cautionLabels)
            ->map(function ($label, $key) use ($vehicle) {
                $value = $vehicle->caution_notes[$key] ?? null;
                return [
                    'key' => $key,
                    'label' => $label,
                    'value' => $value,
                ];
            })
            ->filter(fn ($section) => filled($section['value']))
            ->values();

        $fuelDisplay = $vehicle->fuel_type === 'その他'
            ? ($vehicle->fuel_type_other ?: 'その他')
            : $vehicle->fuel_type;

        $specs = collect([
            ['label' => 'メーカー', 'value' => $vehicle->display_maker],
            ['label' => '車種名', 'value' => $vehicle->model],
            ['label' => '年式', 'value' => $vehicle->year ? $vehicle->year . '年' : null],
            ['label' => 'ボディタイプ', 'value' => $vehicle->body_type ? implode(' / ', $vehicle->body_type) : null],
            ['label' => '排気量カテゴリ', 'value' => $vehicle->displacement_categories ? implode(' / ', $vehicle->displacement_categories) : null],
            ['label' => 'ミッション', 'value' => $vehicle->transmission],
            ['label' => '駆動方式', 'value' => $vehicle->drivetrain],
            ['label' => '乗車定員', 'value' => $vehicle->display_seats],
            ['label' => '車両重量', 'value' => $vehicle->weight ? $vehicle->weight . 'kg' : null],
            ['label' => 'シート高', 'value' => $vehicle->seat_height ? $vehicle->seat_height . 'mm' : null],
            ['label' => '燃料', 'value' => $fuelDisplay],
        ])->filter(fn ($spec) => filled($spec['value']))->values();

        $isFavorited = $vehicle->isFavoritedBy($user);
        $favoritesCount = $vehicle->favorites()->count();

        $imageUrls = collect($vehicle->images ?? [])
            ->map(fn ($path) => Storage::url($path))
            ->all();

        return view('vehicles.show', [
            'vehicle' => $vehicle,
            'sharingPeriods' => $sharingPeriods,
            'cautionSections' => $cautionSections,
            'fuelDisplay' => $fuelDisplay,
            'specs' => $specs,
            'imageUrls' => $imageUrls,
            'isFavorited' => $isFavorited,
            'favoritesCount' => $favoritesCount,
        ]);
    }

    public function store(Request $request)
    {
        $currentYear = $this->vehicleYearUpperBound();

        $baseRules = [
            'type' => ['required', Rule::in(['car', 'bike'])],
            'prefecture_id' => ['required', 'exists:prefectures,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'maker' => ['required', 'string', 'max:100'],
            'maker_custom' => ['nullable', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'fuel_type' => ['required', Rule::in(['レギュラー', 'ハイオク', '軽油', 'EV', '水素', 'その他'])],
            'fuel_type_other' => ['nullable', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:1980', 'max:'.$currentYear],
            'body_type' => ['required', 'string'],
            'transmission' => ['required', Rule::in(self::CAR_TRANSMISSIONS)],
            'drivetrain' => ['nullable', 'string', 'max:50'],
            'photos' => ['required', 'array', 'min:1', 'max:20'],
            'photos.*' => ['image', 'max:5120'],
            'price' => ['required', 'integer', 'min:1000', 'max:100000', 'multiple_of:1000'],
            'sharing_periods' => ['nullable', 'string'],
            'caution_operation' => ['nullable', 'string', 'max:500'],
            'caution_driving' => ['nullable', 'string', 'max:500'],
            'caution_capacity' => ['nullable', 'string', 'max:500'],
            'caution_return' => ['nullable', 'string', 'max:500'],
            'caution_refuel' => ['nullable', 'string', 'max:500'],
            'caution_other' => ['nullable', 'string', 'max:500'],
        ];

        $type = $request->input('type');

        if ($type === 'car') {
            $additionalRules = [
                'seats_choice' => ['required', Rule::in(self::CAR_SEAT_OPTIONS)],
                'drivetrain' => ['required', Rule::in(self::CAR_DRIVETRAINS)],
            ];
        } else {
            $additionalRules = [
                'displacement_categories' => ['required', 'string'],
                'drivetrain' => ['required', Rule::in(self::BIKE_DRIVETRAINS)],
                'weight' => ['required', 'integer', 'min:50', 'max:500'],
                'seat_height' => ['required', 'integer', 'min:600', 'max:1000'],
            ];
        }

        try {
            $validated = $request->validate(array_merge($baseRules, $additionalRules));
        } catch (ValidationException $exception) {
            Log::channel('stack')->warning('車両登録のバリデーションに失敗しました', [
                'errors' => $exception->errors(),
            ]);

            throw $exception;
        }

        if ($validated['fuel_type'] === 'その他' && blank($validated['fuel_type_other'])) {
            Log::channel('stack')->warning('車両登録エラー: fuel_type_other が未入力');

            return back()->withErrors(['fuel_type_other' => '燃料の種類を入力してください。'])->withInput();
        }

        if ($validated['price'] % 1000 !== 0) {
            Log::channel('stack')->warning('車両登録エラー: price が 1,000 円単位ではありません', [
                'price' => $validated['price'],
            ]);

            return back()->withErrors(['price' => '料金は1,000円単位で選択してください。'])->withInput();
        }

        $fuelTypeOther = $validated['fuel_type'] === 'その他' ? $validated['fuel_type_other'] : null;

        $cautionNotes = [
            'operation' => $request->input('caution_operation'),
            'driving' => $request->input('caution_driving'),
            'capacity' => $request->input('caution_capacity'),
            'return' => $request->input('caution_return'),
            'refuel' => $request->input('caution_refuel'),
            'other' => $request->input('caution_other'),
        ];
        $cautionNotes = array_filter($cautionNotes, fn ($value) => filled($value));

        $photos = $request->file('photos', []);
        $imagePaths = [];
        foreach (array_slice($photos, 0, 20) as $photo) {
            if (! $photo->isValid()) {
                Log::channel('stack')->warning('車両登録エラー: アップロードに失敗した写真ファイル', [
                    'error' => $photo->getError(),
                    'errorMessage' => $photo->getErrorMessage(),
                    'size' => $photo->getSize(),
                    'originalName' => $photo->getClientOriginalName(),
                ]);

                return back()->withErrors([
                    'photos' => '写真のアップロードに失敗しました。ファイルサイズ（5MB以下）や形式をご確認ください。',
                ])->withInput();
            }

            $storedPath = $this->processAndStorePhoto($photo);
            if (! $storedPath) {
                return back()->withErrors([
                    'photos' => '写真の処理に失敗しました。別の画像でお試しください。',
                ])->withInput();
            }

            $imagePaths[] = $storedPath;
        }

        if (empty($imagePaths)) {
            Log::channel('stack')->warning('車両登録エラー: 画像がアップロードされていません');

            return back()->withErrors(['photos' => '車両写真のアップロードに失敗しました。'])->withInput();
        }

        $description = null;
        if (! empty($cautionNotes)) {
            $labelMap = [
                'operation' => '操作',
                'driving' => '走行',
                'capacity' => '乗車定員',
                'return' => '返却',
                'refuel' => '給油',
                'other' => 'その他',
            ];
            $description = collect($cautionNotes)
                ->map(fn ($value, $key) => '【' . ($labelMap[$key] ?? $key) . '】' . $value)
                ->implode(PHP_EOL);
        }

        if (! City::where('id', $validated['city_id'])->where('prefecture_id', $validated['prefecture_id'])->exists()) {
            return back()->withErrors(['city_id' => '選択した市区町村が都道府県と一致しません。'])->withInput();
        }

        $bodyTypes = $this->decodeJsonField($request->input('body_type'));
        if (empty($bodyTypes)) {
            return back()->withErrors(['body_type' => '少なくとも1つ選択してください。'])->withInput();
        }

        $sharingPeriods = $this->decodeJsonField($request->input('sharing_periods'));
        $displacementCategories = $type === 'bike'
            ? $this->decodeJsonField($request->input('displacement_categories'))
            : null;

        $fuelTypeOther = $validated['fuel_type'] === 'その他' ? $validated['fuel_type_other'] : null;

        $maker = $validated['maker'];
        $makerCustom = $maker === 'その他' ? $validated['maker_custom'] : null;

        $seatsChoice = $request->input('seats_choice');
        $seatsValue = null;
        if ($type === 'car' && $seatsChoice) {
            $seatsValue = $seatsChoice === '9+' ? 9 : (int) $seatsChoice;
        }

        $vehicle = Vehicle::create([
            'user_id' => Auth::id(),
            'type' => $type,
            'prefecture_id' => $validated['prefecture_id'],
            'city_id' => $validated['city_id'],
            'maker' => $maker,
            'maker_custom' => $makerCustom,
            'model' => $validated['model'],
            'fuel_type' => $validated['fuel_type'],
            'fuel_type_other' => $fuelTypeOther,
            'year' => $validated['year'],
            'body_type' => $bodyTypes,
            'displacement_categories' => $displacementCategories,
            'transmission' => $validated['transmission'],
            'drivetrain' => $validated['drivetrain'] ?? null,
            'seats' => $seatsValue,
            'weight' => $type === 'bike' ? ($validated['weight'] ?? null) : null,
            'seat_height' => $type === 'bike' ? ($validated['seat_height'] ?? null) : null,
            'price_per_day' => $validated['price'],
            'sharing_periods' => $sharingPeriods,
            'description' => $description,
            'caution_notes' => $cautionNotes ?: null,
            'images' => $imagePaths,
        ]);

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('vehicle_registered_message', '登録が完了しました。新しいオーナー体験をスタートしましょう！');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $this->ensureOwnedByCurrentUser($vehicle);

        $currentYear = $this->vehicleYearUpperBound();

        $baseRules = [
            'type' => ['required', Rule::in(['car', 'bike'])],
            'prefecture_id' => ['required', 'exists:prefectures,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'maker' => ['required', 'string', 'max:100'],
            'maker_custom' => ['nullable', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'fuel_type' => ['required', Rule::in(['レギュラー', 'ハイオク', '軽油', 'EV', '水素', 'その他'])],
            'fuel_type_other' => ['nullable', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:1980', 'max:'.$currentYear],
            'body_type' => ['required', 'string'],
            'transmission' => ['required', Rule::in(self::CAR_TRANSMISSIONS)],
            'drivetrain' => ['nullable', 'string', 'max:50'],
            'photos' => ['nullable', 'array', 'max:20'],
            'photos.*' => ['image', 'max:5120'],
            'price' => ['required', 'integer', 'min:1000', 'max:100000', 'multiple_of:1000'],
            'sharing_periods' => ['nullable', 'string'],
            'caution_operation' => ['nullable', 'string', 'max:500'],
            'caution_driving' => ['nullable', 'string', 'max:500'],
            'caution_capacity' => ['nullable', 'string', 'max:500'],
            'caution_return' => ['nullable', 'string', 'max:500'],
            'caution_refuel' => ['nullable', 'string', 'max:500'],
            'caution_other' => ['nullable', 'string', 'max:500'],
            'existing_images' => ['nullable', 'string'],
        ];

        $type = $request->input('type');

        if ($type === 'car') {
            $additionalRules = [
                'seats_choice' => ['required', Rule::in(self::CAR_SEAT_OPTIONS)],
                'drivetrain' => ['required', Rule::in(self::CAR_DRIVETRAINS)],
            ];
        } else {
            $additionalRules = [
                'displacement_categories' => ['required', 'string'],
                'drivetrain' => ['required', Rule::in(self::BIKE_DRIVETRAINS)],
                'weight' => ['required', 'integer', 'min:50', 'max:500'],
                'seat_height' => ['required', 'integer', 'min:600', 'max:1000'],
            ];
        }

        $validated = $request->validate(array_merge($baseRules, $additionalRules));

        if ($validated['fuel_type'] === 'その他' && blank($validated['fuel_type_other'])) {
            return back()->withErrors(['fuel_type_other' => '燃料の種類を入力してください。'])->withInput();
        }

        if ($validated['price'] % 1000 !== 0) {
            return back()->withErrors(['price' => '料金は1,000円単位で選択してください。'])->withInput();
        }

        if (! City::where('id', $validated['city_id'])->where('prefecture_id', $validated['prefecture_id'])->exists()) {
            return back()->withErrors(['city_id' => '選択した市区町村が都道府県と一致しません。'])->withInput();
        }

        $bodyTypes = $this->decodeJsonField($request->input('body_type'));
        if (empty($bodyTypes)) {
            return back()->withErrors(['body_type' => '少なくとも1つ選択してください。'])->withInput();
        }

        $sharingPeriods = $this->decodeJsonField($request->input('sharing_periods'));
        $displacementCategories = $type === 'bike'
            ? $this->decodeJsonField($request->input('displacement_categories'))
            : null;

        $fuelTypeOther = $validated['fuel_type'] === 'その他' ? $validated['fuel_type_other'] : null;

        $existingImagesInput = $this->decodeJsonField($request->input('existing_images'));
        $existingImages = collect($existingImagesInput)
            ->filter(fn ($path) => in_array($path, $vehicle->images ?? [], true))
            ->values()
            ->all();

        $photos = $request->file('photos', []);
        $remainingSlots = max(0, 20 - count($existingImages));
        $newImagePaths = [];

        foreach (array_slice($photos, 0, $remainingSlots) as $photo) {
            if (! $photo->isValid()) {
                Log::channel('stack')->warning('車両更新エラー: アップロードに失敗した写真ファイル', [
                    'error' => $photo->getError(),
                    'errorMessage' => $photo->getErrorMessage(),
                    'size' => $photo->getSize(),
                    'originalName' => $photo->getClientOriginalName(),
                ]);

                return back()->withErrors([
                    'photos' => '写真のアップロードに失敗しました。ファイルサイズ（5MB以下）や形式をご確認ください。',
                ])->withInput();
            }

            $storedPath = $this->processAndStorePhoto($photo);
            if (! $storedPath) {
                return back()->withErrors([
                    'photos' => '写真の処理に失敗しました。別の画像でお試しください。',
                ])->withInput();
            }

            $newImagePaths[] = $storedPath;
        }

        $imagePaths = array_merge($existingImages, $newImagePaths);

        if (empty($imagePaths)) {
            return back()->withErrors(['photos' => '車両写真を少なくとも1枚保持してください。'])->withInput();
        }

        $cautionNotes = [
            'operation' => $request->input('caution_operation'),
            'driving' => $request->input('caution_driving'),
            'capacity' => $request->input('caution_capacity'),
            'return' => $request->input('caution_return'),
            'refuel' => $request->input('caution_refuel'),
            'other' => $request->input('caution_other'),
        ];
        $cautionNotes = array_filter($cautionNotes, fn ($value) => filled($value));

        $description = null;
        if (! empty($cautionNotes)) {
            $labelMap = [
                'operation' => '操作',
                'driving' => '走行',
                'capacity' => '乗車定員',
                'return' => '返却',
                'refuel' => '給油',
                'other' => 'その他',
            ];
            $description = collect($cautionNotes)
                ->map(fn ($value, $key) => '【' . ($labelMap[$key] ?? $key) . '】' . $value)
                ->implode(PHP_EOL);
        }

        $maker = $validated['maker'];
        $makerCustom = $maker === 'その他' ? $validated['maker_custom'] : null;

        $seatsChoice = $request->input('seats_choice');
        $seatsValue = null;
        if ($type === 'car' && $seatsChoice) {
            $seatsValue = $seatsChoice === '9+' ? 9 : (int) $seatsChoice;
        }

        $vehicle->update([
            'type' => $type,
            'prefecture_id' => $validated['prefecture_id'],
            'city_id' => $validated['city_id'],
            'maker' => $maker,
            'maker_custom' => $makerCustom,
            'model' => $validated['model'],
            'fuel_type' => $validated['fuel_type'],
            'fuel_type_other' => $fuelTypeOther,
            'year' => $validated['year'],
            'body_type' => $bodyTypes,
            'displacement_categories' => $displacementCategories,
            'transmission' => $validated['transmission'],
            'drivetrain' => $validated['drivetrain'] ?? null,
            'seats' => $seatsValue,
            'weight' => $type === 'bike' ? ($validated['weight'] ?? null) : null,
            'seat_height' => $type === 'bike' ? ($validated['seat_height'] ?? null) : null,
            'price_per_day' => $validated['price'],
            'sharing_periods' => $sharingPeriods,
            'description' => $description,
            'caution_notes' => $cautionNotes ?: null,
            'images' => $imagePaths,
        ]);

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('vehicle_updated_message', '掲載内容を更新しました。変更がすぐに反映されます。');
    }

    public function getCities($prefectureId)
    {
        $cities = City::where('prefecture_id', $prefectureId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    private function normalizeMakerGroups(array $groups): array
    {
        return collect($groups)
            ->map(fn ($options, $label) => ['label' => $label, 'options' => $options])
            ->values()
            ->all();
    }

    private function sanitizeArray($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_unique($value), fn ($item) => filled($item)));
    }

    private function decodeJsonField(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE
            ? array_values(array_filter($decoded, fn ($item) => filled($item)))
            : [];
    }

    private function ensureOwnedByCurrentUser(Vehicle $vehicle): void
    {
        abort_unless(Auth::id() === $vehicle->user_id, 403);
    }

    private function processAndStorePhoto(UploadedFile $photo): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            Log::channel('stack')->warning('画像処理をスキップ: GD拡張が利用できません');

            return $photo->store('vehicles', 'public');
        }

        try {
            $realPath = $photo->getRealPath();
            if (! $realPath || ! is_readable($realPath)) {
                Log::channel('stack')->warning('画像処理エラー: 一時ファイルを読み込めません', [
                    'originalName' => $photo->getClientOriginalName(),
                ]);

                return null;
            }

            $imageData = @file_get_contents($realPath);
            if ($imageData === false) {
                Log::channel('stack')->warning('画像処理エラー: ファイル読み込みに失敗しました', [
                    'originalName' => $photo->getClientOriginalName(),
                ]);

                return null;
            }

            $resource = @imagecreatefromstring($imageData);
            if (! $resource) {
                Log::channel('stack')->warning('画像処理エラー: GDで画像を生成できません', [
                    'mime' => $photo->getMimeType(),
                    'originalName' => $photo->getClientOriginalName(),
                ]);

                return $photo->store('vehicles', 'public');
            }

            $resource = $this->applyOrientation($resource, $photo);

            $width = imagesx($resource);
            $height = imagesy($resource);
            $maxDimension = 1920;
            $scale = min($maxDimension / $width, $maxDimension / $height, 1);

            $targetWidth = (int) round($width * $scale);
            $targetHeight = (int) round($height * $scale);

            if ($scale < 1) {
                $resized = imagecreatetruecolor($targetWidth, $targetHeight);

                if ($this->isPngLike($photo)) {
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                    imagefilledrectangle($resized, 0, 0, $targetWidth, $targetHeight, $transparent);
                } else {
                    $white = imagecolorallocate($resized, 255, 255, 255);
                    imagefilledrectangle($resized, 0, 0, $targetWidth, $targetHeight, $white);
                }

                imagecopyresampled($resized, $resource, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
                imagedestroy($resource);
                $resource = $resized;
            }

            $encoded = $this->encodeImage($resource, $photo->getMimeType());
            imagedestroy($resource);

            if ($encoded === null) {
                Log::channel('stack')->warning('画像処理エラー: エンコードに失敗しました', [
                    'mime' => $photo->getMimeType(),
                    'originalName' => $photo->getClientOriginalName(),
                ]);

                return null;
            }

            $extension = $encoded['extension'];
            $contents = $encoded['contents'];
            $filename = 'vehicles/' . uniqid('vehicle_', true) . '.' . $extension;

            Storage::disk('public')->put($filename, $contents);

            return $filename;
        } catch (\Throwable $e) {
            Log::channel('stack')->error('画像処理中に例外が発生しました', [
                'message' => $e->getMessage(),
                'originalName' => $photo->getClientOriginalName(),
            ]);

            return null;
        }
    }

    private function applyOrientation($resource, UploadedFile $photo)
    {
        $mime = $photo->getMimeType();
        if (! \in_array($mime, ['image/jpeg', 'image/jpg', 'image/pjpeg'], true)) {
            return $resource;
        }

        if (! function_exists('exif_read_data')) {
            return $resource;
        }

        $realPath = $photo->getRealPath();
        if (! $realPath) {
            return $resource;
        }

        $exif = @exif_read_data($realPath);
        if (! $exif || empty($exif['Orientation'])) {
            return $resource;
        }

        switch ((int) $exif['Orientation']) {
            case 3:
                $resource = imagerotate($resource, 180, 0);
                break;
            case 6:
                $resource = imagerotate($resource, -90, 0);
                break;
            case 8:
                $resource = imagerotate($resource, 90, 0);
                break;
        }

        return $resource;
    }

    private function encodeImage($resource, ?string $mime): ?array
    {
        ob_start();

        $extension = 'jpg';
        $success = false;

        if ($this->isPngLikeMime($mime)) {
            imagealphablending($resource, false);
            imagesavealpha($resource, true);
            $success = imagepng($resource, null, 6);
            $extension = 'png';
        } elseif ($mime === 'image/webp' && function_exists('imagewebp')) {
            $success = imagewebp($resource, null, 80);
            $extension = 'webp';
        } else {
            $success = imagejpeg($resource, null, 82);
            $extension = 'jpg';
        }

        $contents = ob_get_clean();

        if (! $success || $contents === false) {
            return null;
        }

        return [
            'extension' => $extension,
            'contents' => $contents,
        ];
    }

    private function isPngLike(UploadedFile $photo): bool
    {
        return $this->isPngLikeMime($photo->getMimeType());
    }

    private function isPngLikeMime(?string $mime): bool
    {
        return \in_array($mime, ['image/png', 'image/x-png'], true);
    }

    private function buildOldValuesFromVehicle(Vehicle $vehicle): array
    {
        return [
            'type' => $vehicle->type,
            'prefecture_id' => $vehicle->prefecture_id,
            'city_id' => $vehicle->city_id,
            'maker' => $vehicle->maker,
            'maker_custom' => $vehicle->maker_custom,
            'model' => $vehicle->model,
            'fuel_type' => $vehicle->fuel_type,
            'fuel_type_other' => $vehicle->fuel_type_other,
            'year' => $vehicle->year,
            'body_type' => $vehicle->body_type ?? [],
            'displacement_categories' => $vehicle->displacement_categories ?? [],
            'transmission' => $vehicle->transmission,
            'drivetrain' => $vehicle->drivetrain,
            'seats_choice' => $vehicle->seats ? ($vehicle->seats >= 9 ? '9+' : (string) $vehicle->seats) : '',
            'weight' => $vehicle->weight,
            'seat_height' => $vehicle->seat_height,
            'price' => $vehicle->price_per_day,
            'sharing_periods' => $vehicle->sharing_periods ?? [],
            'caution_operation' => $vehicle->caution_notes['operation'] ?? '',
            'caution_driving' => $vehicle->caution_notes['driving'] ?? '',
            'caution_capacity' => $vehicle->caution_notes['capacity'] ?? '',
            'caution_return' => $vehicle->caution_notes['return'] ?? '',
            'caution_refuel' => $vehicle->caution_notes['refuel'] ?? '',
            'caution_other' => $vehicle->caution_notes['other'] ?? '',
        ];
    }

    private function buildOldValuesFromArray(array $oldInput): array
    {
        return [
            'type' => $oldInput['type'] ?? '',
            'prefecture_id' => $oldInput['prefecture_id'] ?? '',
            'city_id' => $oldInput['city_id'] ?? '',
            'maker' => $oldInput['maker'] ?? '',
            'maker_custom' => $oldInput['maker_custom'] ?? '',
            'model' => $oldInput['model'] ?? '',
            'fuel_type' => $oldInput['fuel_type'] ?? '',
            'fuel_type_other' => $oldInput['fuel_type_other'] ?? '',
            'year' => $oldInput['year'] ?? '',
            'body_type' => $this->decodeJsonField($oldInput['body_type'] ?? null),
            'displacement_categories' => $this->decodeJsonField($oldInput['displacement_categories'] ?? null),
            'transmission' => $oldInput['transmission'] ?? '',
            'drivetrain' => $oldInput['drivetrain'] ?? '',
            'seats_choice' => $oldInput['seats_choice'] ?? '',
            'weight' => $oldInput['weight'] ?? '',
            'seat_height' => $oldInput['seat_height'] ?? '',
            'price' => $oldInput['price'] ?? '',
            'sharing_periods' => $this->decodeJsonField($oldInput['sharing_periods'] ?? null),
            'caution_operation' => $oldInput['caution_operation'] ?? '',
            'caution_driving' => $oldInput['caution_driving'] ?? '',
            'caution_capacity' => $oldInput['caution_capacity'] ?? '',
            'caution_return' => $oldInput['caution_return'] ?? '',
            'caution_refuel' => $oldInput['caution_refuel'] ?? '',
            'caution_other' => $oldInput['caution_other'] ?? '',
        ];
    }

    private function vehicleYearUpperBound(): int
    {
        return (int) now()->year;
    }
}
