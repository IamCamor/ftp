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
        Schema::create('fishing_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('point_id')->nullable()->constrained()->onDelete('set null');
            
            // Основная информация о сессии
            $table->string('name')->nullable(); // Название сессии
            $table->text('description')->nullable(); // Описание
            $table->timestamp('started_at'); // Время начала
            $table->timestamp('ended_at')->nullable(); // Время окончания
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
            
            // Настройки сессии
            $table->string('watch_hand')->nullable(); // На какой руке часы (casting/reeling)
            $table->boolean('biometric_tracking')->default(true); // Включено ли отслеживание биометрии
            $table->boolean('gps_tracking')->default(true); // Включено ли GPS отслеживание
            $table->boolean('mood_tracking')->default(true); // Включено ли отслеживание настроения
            
            // Статистика сессии
            $table->integer('total_casts')->default(0); // Общее количество забросов
            $table->integer('total_reels')->default(0); // Общее количество оборотов катушки
            $table->decimal('total_reels_meters', 10, 2)->default(0); // Общие метры подмотки
            $table->integer('catches_count')->default(0); // Количество пойманной рыбы
            $table->decimal('total_weight', 8, 2)->default(0); // Общий вес улова
            
            // Биометрическая статистика
            $table->integer('avg_heart_rate')->nullable(); // Средний пульс
            $table->integer('max_heart_rate')->nullable(); // Максимальный пульс
            $table->integer('min_heart_rate')->nullable(); // Минимальный пульс
            $table->decimal('avg_hrv', 8, 2)->nullable(); // Средняя HRV
            $table->decimal('avg_stress_level', 5, 2)->nullable(); // Средний уровень стресса
            $table->decimal('avg_mood_index', 5, 2)->nullable(); // Средний индекс настроения
            $table->decimal('max_mood_index', 5, 2)->nullable(); // Максимальный индекс настроения
            $table->decimal('min_mood_index', 5, 2)->nullable(); // Минимальный индекс настроения
            
            // Время в разных состояниях (в минутах)
            $table->integer('time_high_mood')->default(0); // Время в высоком настроении
            $table->integer('time_medium_mood')->default(0); // Время в среднем настроении
            $table->integer('time_low_mood')->default(0); // Время в низком настроении
            $table->integer('time_stressed')->default(0); // Время в состоянии стресса
            $table->integer('time_calm')->default(0); // Время в спокойном состоянии
            
            // GPS данные
            $table->decimal('start_latitude', 10, 7)->nullable(); // Широта начала
            $table->decimal('start_longitude', 11, 7)->nullable(); // Долгота начала
            $table->decimal('end_latitude', 10, 7)->nullable(); // Широта окончания
            $table->decimal('end_longitude', 11, 7)->nullable(); // Долгота окончания
            $table->decimal('total_distance', 10, 2)->default(0); // Общее расстояние (км)
            
            // Погодные условия
            $table->decimal('temperature', 5, 2)->nullable(); // Температура
            $table->integer('humidity')->nullable(); // Влажность
            $table->decimal('wind_speed', 5, 2)->nullable(); // Скорость ветра
            $table->string('weather_condition')->nullable(); // Погодные условия
            
            // Дополнительные данные
            $table->json('gps_track')->nullable(); // GPS трек
            $table->json('mood_timeline')->nullable(); // Временная линия настроения
            $table->json('heart_rate_timeline')->nullable(); // Временная линия пульса
            $table->json('activity_timeline')->nullable(); // Временная линия активности
            $table->text('session_summary')->nullable(); // Резюме сессии
            $table->text('coach_insights')->nullable(); // Советы тренера
            
            $table->timestamps();
            
            // Индексы
            $table->index(['user_id', 'started_at']);
            $table->index(['point_id', 'started_at']);
            $table->index('status');
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishing_sessions');
    }
};