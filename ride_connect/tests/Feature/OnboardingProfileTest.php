<?php

namespace Tests\Feature;

use App\Models\Prefecture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_complete_onboarding_with_license_years(): void
    {
        $user = User::factory()->create();

        $prefecture = Prefecture::create([
            'name' => '東京都',
        ]);

        $payload = [
            'nickname' => 'テストユーザー',
            'prefecture_id' => $prefecture->id,
            'region' => '東京都',
            'city' => '新宿区',
            'license_type' => ['普通免許', '普通自動二輪免許（AT限定）'],
            'license_years_meta' => [
                '普通免許' => '3年以上',
                '普通自動二輪免許（AT限定）' => '1年以上',
            ],
            'license_years_meta_json' => json_encode([
                '普通免許' => '3年以上',
                '普通自動二輪免許（AT限定）' => '1年以上',
            ], JSON_UNESCAPED_UNICODE),
            'driving_frequency_car' => '毎日',
            'driving_frequency_bike' => '年に数回',
            'owns_none' => 1,
            'owned_vehicles' => json_encode([]),
            'height' => '170',
            'weight' => '60',
            'bio' => 'よろしくお願いします。',
            'gender' => '男性',
            'age' => 28,
        ];

        $response = $this
            ->actingAs($user)
            ->from(route('onboarding.profile'))
            ->post(route('onboarding.profile.store'), $payload);

        $response->assertRedirect(route('onboarding.profile.complete'));

        $user->refresh();

        $this->assertEquals(['普通免許', '普通自動二輪免許（AT限定）'], $user->profile->license_type);
        $this->assertEquals([
            '普通免許' => '3年以上',
            '普通自動二輪免許（AT限定）' => '1年以上',
        ], $user->profile->license_years_meta);
        $this->assertSame('男性', $user->profile->gender);
        $this->assertSame(28, $user->profile->age);
    }

    public function test_license_years_meta_json_is_used_when_array_missing(): void
    {
        $user = User::factory()->create();

        $prefecture = Prefecture::create([
            'name' => '東京都',
        ]);

        $payload = [
            'nickname' => 'テストユーザー',
            'prefecture_id' => $prefecture->id,
            'region' => '東京都',
            'city' => '新宿区',
            'license_type' => ['普通免許', '大型二輪免許'],
            'license_years_meta_json' => json_encode([
                '普通免許' => '2年以上',
                '大型二輪免許' => '4年以上',
            ], JSON_UNESCAPED_UNICODE),
            'driving_frequency_car' => '毎日',
            'driving_frequency_bike' => '年に数回',
            'owns_none' => 1,
            'owned_vehicles' => json_encode([]),
            'gender' => '女性',
            'age' => 34,
        ];

        $response = $this
            ->actingAs($user)
            ->post(route('onboarding.profile.store'), $payload);

        $response->assertRedirect(route('onboarding.profile.complete'));

        $user->refresh();

        $this->assertEquals([
            '普通免許' => '2年以上',
            '大型二輪免許' => '4年以上',
        ], $user->profile->license_years_meta);
        $this->assertSame('女性', $user->profile->gender);
        $this->assertSame(34, $user->profile->age);
    }

    public function test_onboarding_allows_car_only_license_without_bike_frequency(): void
    {
        $user = User::factory()->create();

        $prefecture = Prefecture::create([
            'name' => '東京都',
        ]);

        $payload = [
            'nickname' => 'テストユーザー',
            'prefecture_id' => $prefecture->id,
            'region' => '東京都',
            'city' => '新宿区',
            'license_type' => ['普通免許'],
            'license_years_meta' => [
                '普通免許' => '1年以上',
            ],
            'driving_frequency_car' => '毎日',
            'driving_frequency_bike' => '',
            'owns_none' => 1,
            'owned_vehicles' => json_encode([]),
            'gender' => '未回答',
            'age' => 40,
        ];

        $response = $this
            ->actingAs($user)
            ->post(route('onboarding.profile.store'), $payload);

        $response->assertRedirect(route('onboarding.profile.complete'));

        $user->refresh();

        $this->assertEquals(['普通免許'], $user->profile->license_type);
        $this->assertEquals(['普通免許' => '1年以上'], $user->profile->license_years_meta);
        $this->assertSame('毎日', $user->profile->driving_frequency_car);
        $this->assertNull($user->profile->driving_frequency_bike);
        $this->assertSame('未回答', $user->profile->gender);
        $this->assertSame(40, $user->profile->age);
    }
}
