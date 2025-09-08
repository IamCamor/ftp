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
        Schema::create('fishing_knots', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название узла
            $table->string('slug')->unique(); // URL slug
            $table->text('description')->nullable(); // Описание
            $table->text('purpose')->nullable(); // Назначение узла
            $table->text('instructions')->nullable(); // Пошаговая инструкция
            $table->string('difficulty')->default('medium'); // easy, medium, hard
            $table->decimal('strength_percentage', 5, 2)->nullable(); // Прочность в %
            $table->string('photo_url')->nullable(); // Фото готового узла
            $table->json('step_photos')->nullable(); // Фото пошагового завязывания
            $table->json('video_urls')->nullable(); // Видео инструкции
            $table->json('use_cases')->nullable(); // Случаи применения
            $table->json('line_types')->nullable(); // Типы лесок
            $table->integer('view_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['name', 'is_active']);
            $table->index(['difficulty', 'is_active']);
            $table->index(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishing_knots');
    }
};
