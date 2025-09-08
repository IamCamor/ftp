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
        Schema::create('boat_engines', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название мотора
            $table->string('brand')->nullable(); // Бренд
            $table->string('model')->nullable(); // Модель
            $table->string('slug')->unique(); // URL slug
            $table->text('description')->nullable(); // Описание
            $table->string('type')->default('outboard'); // outboard, inboard, electric
            $table->string('fuel_type')->nullable(); // petrol, diesel, electric, hybrid
            $table->integer('power_hp')->nullable(); // Мощность в л.с.
            $table->integer('power_kw')->nullable(); // Мощность в кВт
            $table->integer('weight')->nullable(); // Вес (кг)
            $table->integer('cylinders')->nullable(); // Количество цилиндров
            $table->string('displacement')->nullable(); // Объем двигателя
            $table->json('specifications')->nullable(); // Технические характеристики
            $table->json('features')->nullable(); // Особенности
            $table->decimal('price_min', 10, 2)->nullable(); // Минимальная цена
            $table->decimal('price_max', 10, 2)->nullable(); // Максимальная цена
            $table->string('photo_url')->nullable(); // Фото мотора
            $table->json('additional_photos')->nullable(); // Дополнительные фото
            $table->json('pros')->nullable(); // Преимущества
            $table->json('cons')->nullable(); // Недостатки
            $table->json('best_for')->nullable(); // Лучше всего подходит для
            $table->json('compatible_boats')->nullable(); // Совместимые лодки
            $table->json('maintenance')->nullable(); // Уход и обслуживание
            $table->integer('view_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['name', 'is_active']);
            $table->index(['type', 'is_active']);
            $table->index(['brand', 'is_active']);
            $table->index(['power_hp', 'is_active']);
            $table->index(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boat_engines');
    }
};
