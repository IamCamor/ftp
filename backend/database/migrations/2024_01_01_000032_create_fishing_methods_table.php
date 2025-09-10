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
        Schema::create('fishing_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название способа ловли
            $table->string('slug')->unique(); // URL slug
            $table->text('description')->nullable(); // Описание
            $table->text('technique')->nullable(); // Техника ловли
            $table->text('equipment_needed')->nullable(); // Необходимое снаряжение
            $table->string('difficulty')->default('medium'); // easy, medium, hard
            $table->string('season')->nullable(); // Сезон
            $table->json('best_conditions')->nullable(); // Лучшие условия
            $table->json('target_fish')->nullable(); // Целевые виды рыб
            $table->json('equipment_list')->nullable(); // Список снаряжения
            $table->text('step_by_step')->nullable(); // Пошаговая инструкция
            $table->string('photo_url')->nullable(); // Фото способа ловли
            $table->json('additional_photos')->nullable(); // Дополнительные фото
            $table->json('video_urls')->nullable(); // Видео инструкции
            $table->json('tips')->nullable(); // Советы
            $table->json('common_mistakes')->nullable(); // Частые ошибки
            $table->integer('view_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['name', 'is_active']);
            $table->index(['difficulty', 'is_active']);
            $table->index(['season', 'is_active']);
            $table->index(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishing_methods');
    }
};
