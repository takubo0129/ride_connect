<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('maker_custom')->nullable()->after('maker');
            $table->json('displacement_categories')->nullable()->after('displacement');
            $table->integer('weight')->nullable()->after('drivetrain');
            $table->integer('seat_height')->nullable()->after('weight');
            $table->json('sharing_periods')->nullable()->after('price_per_day');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'maker_custom',
                'displacement_categories',
                'weight',
                'seat_height',
                'sharing_periods',
            ]);
        });
    }
};
