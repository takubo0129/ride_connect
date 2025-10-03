<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitiesSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/data/cities.csv');

        if (!file_exists($file)) {
            $this->command->error("cities.csv が見つかりません: {$file}");
            return;
        }

        // 既存データをリセット（外部キー制約を考慮してtruncateではなくdelete）
        DB::table('cities')->delete();

        $cities = [];
        if (($handle = fopen($file, 'r')) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                $prefectureId = $data[0] ?? null;
                $name = $data[1] ?? null;

                // 見出し行や不正値をスキップ
                if (!is_numeric($prefectureId) || empty($name)) {
                    continue;
                }

                $cities[] = [
                    'prefecture_id' => (int) $prefectureId,
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            fclose($handle);
        }

        if (!empty($cities)) {
            foreach (array_chunk($cities, 500) as $chunk) {
                DB::table('cities')->insert($chunk);
            }
            $this->command->info(count($cities) . " 件の市区町村を登録しました！");
        }
    }
}
