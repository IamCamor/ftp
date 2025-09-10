<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token')->unique(); // Push токен устройства
            $table->string('platform')->default('web'); // web, ios, android
            $table->string('device_id')->nullable(); // ID устройства
            $table->string('device_model')->nullable(); // Модель устройства
            $table->string('app_version')->nullable(); // Версия приложения
            $table->json('capabilities')->nullable(); // Возможности устройства
            $table->boolean('is_active')->default(true); // Активен ли токен
            $table->timestamp('last_used_at')->nullable(); // Последнее использование
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['platform', 'is_active']);
            $table->index(['token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
