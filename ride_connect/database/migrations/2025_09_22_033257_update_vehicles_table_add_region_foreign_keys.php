<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('vehicles', 'prefecture_id')) {
                $table->foreignId('prefecture_id')
                    ->nullable()
                    ->after('type')
                    ->constrained()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('vehicles', 'city_id')) {
                $table->foreignId('city_id')
                    ->nullable()
                    ->after('prefecture_id')
                    ->constrained()
                    ->nullOnDelete();
            }
        });

        if (Schema::hasColumn('vehicles', 'region') || Schema::hasColumn('vehicles', 'city')) {
            $vehicleRegions = DB::table('vehicles')->select('id', 'region', 'city')->get();

            $prefectureMap = DB::table('prefectures')->pluck('id', 'name');
            $cityMap = DB::table('cities')
                ->select('id', 'name', 'prefecture_id')
                ->get()
                ->groupBy(function ($city) {
                    return $city->prefecture_id . '::' . $city->name;
                });

            foreach ($vehicleRegions as $vehicle) {
                $prefectureId = null;
                $cityId = null;

                if ($vehicle->region && isset($prefectureMap[$vehicle->region])) {
                    $prefectureId = $prefectureMap[$vehicle->region];
                }

                if ($vehicle->city) {
                    if ($prefectureId) {
                        $key = $prefectureId . '::' . $vehicle->city;
                        $cityEntry = $cityMap[$key][0] ?? null;
                        $cityId = $cityEntry?->id;
                    }

                    if (! $cityId) {
                        $cityEntry = $cityMap->first(fn ($group) => $group->firstWhere('name', $vehicle->city));
                        $cityId = optional($cityEntry)->first()->id ?? null;
                        $prefectureId = $prefectureId ?: optional($cityEntry)->first()->prefecture_id;
                    }
                }

                if ($prefectureId || $cityId) {
                    DB::table('vehicles')
                        ->where('id', $vehicle->id)
                        ->update([
                            'prefecture_id' => $prefectureId,
                            'city_id' => $cityId,
                        ]);
                }
            }

            Schema::table('vehicles', function (Blueprint $table) {
                if (Schema::hasColumn('vehicles', 'region')) {
                    $table->dropColumn('region');
                }
                if (Schema::hasColumn('vehicles', 'city')) {
                    $table->dropColumn('city');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['prefecture_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['prefecture_id', 'city_id']);

            // 戻す場合は文字列カラムにする
            $table->string('region')->nullable();
            $table->string('city')->nullable();
        });
    }
};
