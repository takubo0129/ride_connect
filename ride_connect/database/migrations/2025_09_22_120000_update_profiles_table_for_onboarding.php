<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->json('license_years_meta')->nullable()->after('license_type');
            $table->string('driving_frequency_car')->nullable()->after('driving_frequency');
            $table->string('driving_frequency_bike')->nullable()->after('driving_frequency_car');
            $table->boolean('onboarding_completed')->default(false)->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'license_years_meta',
                'driving_frequency_car',
                'driving_frequency_bike',
                'onboarding_completed',
            ]);
        });
    }
};
