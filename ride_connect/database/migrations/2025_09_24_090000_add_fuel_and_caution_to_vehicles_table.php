<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('vehicles', 'fuel_type')) {
                $table->string('fuel_type')->after('model');
            }
            if (! Schema::hasColumn('vehicles', 'fuel_type_other')) {
                $table->string('fuel_type_other')->nullable()->after('fuel_type');
            }
            if (! Schema::hasColumn('vehicles', 'caution_notes')) {
                $table->json('caution_notes')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'caution_notes')) {
                $table->dropColumn('caution_notes');
            }
            if (Schema::hasColumn('vehicles', 'fuel_type_other')) {
                $table->dropColumn('fuel_type_other');
            }
            if (Schema::hasColumn('vehicles', 'fuel_type')) {
                $table->dropColumn('fuel_type');
            }
        });
    }
};
