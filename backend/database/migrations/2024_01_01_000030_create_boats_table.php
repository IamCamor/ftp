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
        Schema::create('boats', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название лодки
            $table->string('brand')->nullable(); // Бренд
            $table->string('model')->nullable(); // Модель
            $table->string('slug')->unique(); // URL slug
            $table->text('description')->nullable(); // Описание
            $table->string('type')->default('inflatable'); // inflatable, rigid, kayak, canoe
            $table->integer('length')->nullable(); // Длина (см)
            $table->integer('width')->nullable(); // Ширина (см)
            $table->integer('capacity')->nullable(); // Вместимость (человек)
            $table->integer('max_weight')->nullable(); // Максимальная нагрузка (кг)
            $table->string('material')->nullable(); // Материал
            $table->json('features')->nullable(); // Особенности
            $table->decimal('price_min', 10, 2)->nullable(); // Минимальная цена
            $table->decimal('price_max', 10, 2)->nullable(); // Максимальная цена
            $table->string('photo_url')->nullable(); // Фото лодки
            $table->json('additional_photos')->nullable(); // Дополнительные фото
            $table->json('pros')->nullable(); // Преимущества
            $table->json('cons')->nullable(); // Недостатки
            $table->json('best_for')->nullable(); // Лучше всего подходит для
            $table->integer('view_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['name', 'is_active']);
            $table->index(['type', 'is_active']);
            $table->index(['brand', 'is_active']);
            $table->index(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boats');
    }
};
