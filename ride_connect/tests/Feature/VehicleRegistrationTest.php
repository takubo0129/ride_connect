<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Prefecture;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VehicleRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bike_registration_succeeds_with_valid_payload(): void
    {
        Storage::fake('public');

        $prefecture = Prefecture::create(['name' => '東京都']);
        $city = City::create([
            'prefecture_id' => $prefecture->id,
            'name' => '新宿区',
        ]);

        $user = User::factory()->create();

        $payload = [
            'type' => 'bike',
            'prefecture_id' => $prefecture->id,
            'city_id' => $city->id,
            'maker' => 'ホンダ',
            'maker_custom' => null,
            'model' => 'CB400',
            'fuel_type' => 'レギュラー',
            'fuel_type_other' => null,
            'year' => 2022,
            'body_type' => json_encode(['ネイキッド']),
            'displacement_categories' => json_encode(['401cc〜750cc（大型自動二輪）']),
            'transmission' => 'AT',
            'drivetrain' => 'チェーン',
            'weight' => 190,
            'seat_height' => 780,
            'price' => 12000,
            'sharing_periods' => json_encode(['1day']),
        ];

        $response = $this
            ->actingAs($user)
            ->post(route('vehicles.store'), array_merge($payload, [
                'photos' => [UploadedFile::fake()->image('bike.jpg', 800, 600)],
            ]));

        $vehicle = Vehicle::first();

        $response->assertRedirect(route('vehicles.show', $vehicle));
        $response->assertSessionHas('vehicle_registered_message', '登録が完了しました。新しいオーナー体験をスタートしましょう！');

        $this->assertDatabaseHas('vehicles', [
            'model' => 'CB400',
            'user_id' => $user->id,
        ]);
        $this->assertNotEmpty($vehicle->images);
        Storage::disk('public')->assertExists($vehicle->images[0]);
    }

    public function test_owner_can_update_vehicle_without_new_photos(): void
    {
        Storage::fake('public');

        $prefecture = Prefecture::create(['name' => '神奈川県']);
        $city = City::create([
            'prefecture_id' => $prefecture->id,
            'name' => '藤沢市',
        ]);

        $user = User::factory()->create();

        $existingImage = UploadedFile::fake()->image('existing.jpg')->store('vehicles', 'public');

        $vehicle = Vehicle::create([
            'user_id' => $user->id,
            'type' => 'bike',
            'prefecture_id' => $prefecture->id,
            'city_id' => $city->id,
            'maker' => 'ホンダ',
            'maker_custom' => null,
            'model' => 'CB400',
            'fuel_type' => 'レギュラー',
            'fuel_type_other' => null,
            'year' => 2022,
            'body_type' => ['ネイキッド'],
            'displacement_categories' => ['401cc〜750cc（大型自動二輪）'],
            'transmission' => 'AT',
            'drivetrain' => 'チェーン',
            'weight' => 190,
            'seat_height' => 780,
            'price_per_day' => 12000,
            'sharing_periods' => ['1day'],
            'description' => null,
            'caution_notes' => null,
            'images' => [$existingImage],
        ]);

        $payload = [
            'type' => 'bike',
            'prefecture_id' => $prefecture->id,
            'city_id' => $city->id,
            'maker' => 'ホンダ',
            'maker_custom' => null,
            'model' => 'CB400 改',
            'fuel_type' => 'レギュラー',
            'fuel_type_other' => null,
            'year' => 2023,
            'body_type' => json_encode(['ネイキッド']),
            'displacement_categories' => json_encode(['401cc〜750cc（大型自動二輪）']),
            'transmission' => 'MT',
            'drivetrain' => 'チェーン',
            'weight' => 195,
            'seat_height' => 785,
            'price' => 13000,
            'sharing_periods' => json_encode(['1week']),
            'existing_images' => json_encode([$existingImage]),
        ];

        $response = $this
            ->actingAs($user)
            ->put(route('vehicles.update', $vehicle), $payload);

        $response->assertRedirect(route('vehicles.show', $vehicle->refresh()));
        $response->assertSessionHas('vehicle_updated_message');

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'model' => 'CB400 改',
            'price_per_day' => 13000,
        ]);

        $this->assertSame([$existingImage], $vehicle->images);
        Storage::disk('public')->assertExists($existingImage);
    }
}
