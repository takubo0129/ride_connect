<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nickname', 30);
            $table->string('region'); // 都道府県+市町村
            $table->text('license_type')->nullable(); // 複数免許をJSON文字列で保持
            $table->integer('license_years')->nullable(); // 免許歴
            $table->integer('height')->nullable();
            $table->integer('weight')->nullable();
            $table->text('bio')->nullable(); // 自己紹介文
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
