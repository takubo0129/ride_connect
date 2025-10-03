<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Prefecture;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createPrefectureAndCity(string $prefectureName = '東京都', string $cityName = '新宿区'): array
    {
        $prefecture = Prefecture::firstOrCreate(['name' => $prefectureName]);
        $city = City::firstOrCreate(
            ['prefecture_id' => $prefecture->id, 'name' => $cityName],
            []
        );

        return [$prefecture, $city];
    }

    private function createProfileFor(User $user, array $overrides = []): Profile
    {
        [$prefecture] = $this->createPrefectureAndCity();

        $profile = $user->profile;

        $profile->fill(array_merge([
            'nickname' => 'テストユーザー',
            'region' => $prefecture->name,
            'city' => '新宿区',
            'gender' => '男性',
            'age' => 30,
            'license_type' => ['普通免許'],
            'license_years_meta' => ['普通免許' => '3年以上'],
            'driving_frequency_car' => '毎日',
            'driving_frequency_bike' => '年に数回',
            'owned_vehicle' => null,
            'owned_vehicles' => [
                'cars' => [],
                'bikes' => [],
            ],
            'height' => 170,
            'weight' => 65,
            'bio' => 'よろしくお願いします。',
        ], $overrides));

        $profile->save();

        return $profile->refresh();
    }

    public function test_profile_page_is_displayed(): void
    {
        [$prefecture, $city] = $this->createPrefectureAndCity();
        $user = User::factory()->create();
        $this->createProfileFor($user);

        $response = $this
            ->actingAs($user)
            ->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee($prefecture->name);
    }

    public function test_profile_information_can_be_updated(): void
    {
        [$prefecture, $city] = $this->createPrefectureAndCity('神奈川県', '藤沢市');
        $user = User::factory()->create();
        $profile = $this->createProfileFor($user);

        $payload = [
            'nickname' => '更新ユーザー',
            'prefecture_id' => $prefecture->id,
            'city_id' => $city->id,
            'gender' => '女性',
            'age' => 32,
            'license_type' => ['普通免許', '大型二輪免許'],
            'license_years_meta' => [
                '普通免許' => '4年以上',
                '大型二輪免許' => '1年以上',
            ],
            'driving_frequency_car' => '1週間に1回',
            'driving_frequency_bike' => '年に数回',
            'owned_vehicles_car' => ['トヨタ プリウス'],
            'owned_vehicles_bike' => ['ホンダ スーパーカブ'],
            'height' => 175,
            'weight' => 70,
            'bio' => '安全運転を心掛けています。',
        ];

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), $payload);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $profile->refresh();

        $this->assertSame('更新ユーザー', $profile->nickname);
        $this->assertSame('神奈川県', $profile->region);
        $this->assertSame('藤沢市', $profile->city);
        $this->assertEqualsCanonicalizing(['普通免許', '大型二輪免許'], $profile->license_type);
        $this->assertEquals([
            '普通免許' => '4年以上',
            '大型二輪免許' => '1年以上',
        ], $profile->license_years_meta);
        $this->assertSame('女性', $profile->gender);
        $this->assertSame(32, $profile->age);
        $this->assertSame('1週間に1回', $profile->driving_frequency_car);
        $this->assertSame('年に数回', $profile->driving_frequency_bike);
        $this->assertSame('トヨタ プリウス / ホンダ スーパーカブ', $profile->owned_vehicle);
        $this->assertEquals([
            'cars' => ['トヨタ プリウス'],
            'bikes' => ['ホンダ スーパーカブ'],
        ], $profile->owned_vehicles);
        $this->assertSame(175, $profile->height);
        $this->assertSame(70, $profile->weight);
        $this->assertSame('安全運転を心掛けています。', $profile->bio);
    }


    public function test_profile_update_with_car_only_license_omits_bike_frequency(): void
    {
        [$prefecture, $city] = $this->createPrefectureAndCity('千葉県', '船橋市');
        $user = User::factory()->create();
        $profile = $this->createProfileFor($user);

        $payload = [
            'nickname' => '車ユーザー',
            'prefecture_id' => $prefecture->id,
            'city_id' => $city->id,
            'gender' => '男性',
            'age' => 29,
            'license_type' => ['普通免許'],
            'license_years_meta' => [
                '普通免許' => '2年以上',
            ],
            'driving_frequency_car' => '毎日',
            'driving_frequency_bike' => '',
            'owned_vehicles_car' => [],
            'owned_vehicles_bike' => [],
            'height' => 168,
            'weight' => 60,
            'bio' => 'よろしくお願いします。',
        ];

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), $payload);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $profile->refresh();

        $this->assertEquals(['普通免許'], $profile->license_type);
        $this->assertSame('毎日', $profile->driving_frequency_car);
        $this->assertNull($profile->driving_frequency_bike);
    }

    public function test_profile_update_with_bike_only_license_omits_car_frequency(): void
    {
        [$prefecture, $city] = $this->createPrefectureAndCity('愛知県', '名古屋市');
        $user = User::factory()->create();
        $profile = $this->createProfileFor($user);

        $payload = [
            'nickname' => 'バイクユーザー',
            'prefecture_id' => $prefecture->id,
            'city_id' => $city->id,
            'gender' => '女性',
            'age' => 27,
            'license_type' => ['普通自動二輪免許'],
            'license_years_meta' => [
                '普通自動二輪免許' => '3年以上',
            ],
            'driving_frequency_car' => '',
            'driving_frequency_bike' => '1週間に1回',
            'owned_vehicles_car' => [],
            'owned_vehicles_bike' => ['ホンダ CB400'],
            'height' => 172,
            'weight' => 62,
            'bio' => 'バイクメインです。',
        ];

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), $payload);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $profile->refresh();

        $this->assertEquals(['普通自動二輪免許'], $profile->license_type);
        $this->assertNull($profile->driving_frequency_car);
        $this->assertSame('1週間に1回', $profile->driving_frequency_bike);
    }
    public function test_profile_update_handles_license_none(): void
    {
        [$prefecture, $city] = $this->createPrefectureAndCity('大阪府', '堺市');
        $user = User::factory()->create();
        $profile = $this->createProfileFor($user);

        $payload = [
            'nickname' => '免許なしユーザー',
            'prefecture_id' => $prefecture->id,
            'city_id' => $city->id,
            'gender' => '未回答',
            'age' => 45,
            'license_type' => ['免許なし'],
            'license_years_meta' => [],
            'driving_frequency_car' => '免許なし',
            'driving_frequency_bike' => '免許なし',
            'owned_vehicles_car' => [],
            'owned_vehicles_bike' => [],
            'height' => '',
            'weight' => '',
            'bio' => '',
        ];

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), $payload);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $profile->refresh();

        $this->assertEquals(['免許なし'], $profile->license_type);
        $this->assertSame([], $profile->license_years_meta);
        $this->assertSame('免許なし', $profile->driving_frequency_car);
        $this->assertSame('免許なし', $profile->driving_frequency_bike);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();
        $this->createProfileFor($user);

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();
        $this->createProfileFor($user);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
