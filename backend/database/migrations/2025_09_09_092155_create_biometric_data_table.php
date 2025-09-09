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
        Schema::create('biometric_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('fishing_session_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('catch_record_id')->nullable()->constrained()->onDelete('cascade');
            
            // Основные биометрические данные
            $table->integer('heart_rate')->nullable(); // Пульс (уд/мин)
            $table->decimal('hrv', 8, 2)->nullable(); // Вариабельность сердечного ритма (мс)
            $table->decimal('stress_level', 5, 2)->nullable(); // Уровень стресса (0-100)
            $table->decimal('mood_index', 5, 2)->nullable(); // Индекс настроения (0-100)
            $table->string('mood_emoji')->nullable(); // Эмодзи настроения
            
            // Дополнительные данные
            $table->decimal('temperature', 5, 2)->nullable(); // Температура тела
            $table->integer('steps')->nullable(); // Количество шагов
            $table->decimal('calories_burned', 8, 2)->nullable(); // Сожженные калории
            $table->decimal('activity_level', 5, 2)->nullable(); // Уровень активности (0-100)
            
            // Данные о движении
            $table->decimal('acceleration_x', 10, 6)->nullable(); // Ускорение по X
            $table->decimal('acceleration_y', 10, 6)->nullable(); // Ускорение по Y
            $table->decimal('acceleration_z', 10, 6)->nullable(); // Ускорение по Z
            $table->decimal('gyroscope_x', 10, 6)->nullable(); // Гироскоп по X
            $table->decimal('gyroscope_y', 10, 6)->nullable(); // Гироскоп по Y
            $table->decimal('gyroscope_z', 10, 6)->nullable(); // Гироскоп по Z
            
            // Контекстные данные
            $table->string('watch_hand')->nullable(); // На какой руке часы (casting/reeling)
            $table->integer('casts_count')->nullable(); // Количество забросов
            $table->integer('reels_count')->nullable(); // Количество оборотов катушки
            $table->decimal('reels_meters', 8, 2)->nullable(); // Метры подмотки
            
            // Временные метки
            $table->timestamp('recorded_at'); // Время записи данных
            $table->timestamps();
            
            // Индексы
            $table->index(['user_id', 'recorded_at']);
            $table->index(['fishing_session_id', 'recorded_at']);
            $table->index(['catch_record_id']);
            $table->index('mood_index');
            $table->index('heart_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_data');
    }
};