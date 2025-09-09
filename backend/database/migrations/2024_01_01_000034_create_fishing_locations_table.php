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
        Schema::create('fishing_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название места
            $table->string('slug')->unique(); // URL slug
            $table->text('description')->nullable(); // Описание
            $table->string('type')->default('waterbody'); // waterbody, spot, region, structure
            $table->string('water_type')->nullable(); // river, lake, sea, pond, etc.
            $table->decimal('latitude', 10, 8)->nullable(); // Широта
            $table->decimal('longitude', 11, 8)->nullable(); // Долгота
            $table->string('region')->nullable(); // Регион
            $table->string('country')->nullable(); // Страна
            $table->json('fish_species')->nullable(); // Виды рыб
            $table->json('fishing_methods')->nullable(); // Способы ловли
            $table->json('best_seasons')->nullable(); // Лучшие сезоны
            $table->json('access_info')->nullable(); // Информация о доступе
            $table->json('facilities')->nullable(); // Удобства
            $table->json('regulations')->nullable(); // Правила и ограничения
            $table->string('photo_url')->nullable(); // Фото места
            $table->json('additional_photos')->nullable(); // Дополнительные фото
            $table->json('tips')->nullable(); // Советы
            $table->json('warnings')->nullable(); // Предупреждения
            $table->integer('view_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['name', 'is_active']);
            $table->index(['type', 'is_active']);
            $table->index(['water_type', 'is_active']);
            $table->index(['region', 'is_active']);
            $table->index(['slug']);
            $table->index(['latitude', 'longitude']); // Regular index instead of spatial for SQLite compatibility
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishing_locations');
    }
};
