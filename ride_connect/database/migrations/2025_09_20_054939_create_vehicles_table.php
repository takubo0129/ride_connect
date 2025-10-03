<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 貸主
            $table->enum('type', ['car', 'bike']);
            $table->string('maker');
            $table->string('model');
            $table->integer('year')->nullable();
            $table->string('body_type')->nullable();
            $table->integer('displacement')->nullable(); // 排気量（バイク用）
            $table->string('transmission')->nullable(); // AT/MT
            $table->string('drivetrain')->nullable();   // 2WD/4WD
            $table->integer('seats')->nullable();
            $table->integer('price_per_day');
            $table->text('description')->nullable();
            $table->json('images')->nullable(); // 複数画像
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
