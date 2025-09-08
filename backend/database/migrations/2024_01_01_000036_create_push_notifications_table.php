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
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Заголовок уведомления
            $table->text('body'); // Текст уведомления
            $table->string('type')->default('general'); // Тип уведомления
            $table->json('data')->nullable(); // Дополнительные данные
            $table->string('image_url')->nullable(); // Изображение
            $table->string('action_url')->nullable(); // URL для перехода
            $table->string('action_text')->nullable(); // Текст кнопки действия
            $table->json('target_users')->nullable(); // Целевые пользователи
            $table->string('status')->default('scheduled'); // scheduled, sent, failed
            $table->timestamp('scheduled_at')->nullable(); // Время отправки
            $table->timestamp('sent_at')->nullable(); // Время фактической отправки
            $table->integer('sent_count')->default(0); // Количество отправленных
            $table->integer('failed_count')->default(0); // Количество неудачных
            $table->json('delivery_stats')->nullable(); // Статистика доставки
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Создатель
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index(['type', 'status']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_notifications');
    }
};
