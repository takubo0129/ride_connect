<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Takubo',
            'email' => 'ytakuya17@gmail.com',
            'password' => bcrypt('anzu0818'),
        ]);
        

        // ★ 都道府県・市区町村を投入
        $this->call([
            \Database\Seeders\PrefecturesSeeder::class,
            \Database\Seeders\CitiesSeeder::class,
        ]);
    }
}
