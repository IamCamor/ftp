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
        Schema::create('fish_species', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название рыбы
            $table->string('scientific_name')->nullable(); // Научное название
            $table->string('slug')->unique(); // URL slug
            $table->text('description')->nullable(); // Описание
            $table->text('habitat')->nullable(); // Места обитания
            $table->text('feeding_habits')->nullable(); // Особенности питания
            $table->text('spawning_info')->nullable(); // Информация о нересте
            $table->integer('min_size')->nullable(); // Минимальный размер (см)
            $table->integer('max_size')->nullable(); // Максимальный размер (см)
            $table->decimal('min_weight', 8, 2)->nullable(); // Минимальный вес (кг)
            $table->decimal('max_weight', 8, 2)->nullable(); // Максимальный вес (кг)
            $table->string('photo_url')->nullable(); // Фото рыбы
            $table->json('additional_photos')->nullable(); // Дополнительные фото
            $table->string('category')->default('freshwater'); // freshwater, saltwater, both
            $table->boolean('is_protected')->default(false); // Красная книга
            $table->json('seasons')->nullable(); // Сезоны ловли
            $table->json('best_times')->nullable(); // Лучшее время ловли
            $table->integer('view_count')->default(0); // Количество просмотров
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['name', 'is_active']);
            $table->index(['category', 'is_active']);
            $table->index(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fish_species');
    }
};
