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
        Schema::create('event_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            
            // Статус подписки
            $table->enum('status', ['subscribed', 'unsubscribed', 'hidden'])->default('subscribed');
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('reminders_enabled')->default(true);
            $table->boolean('news_enabled')->default(true);
            
            // Настройки уведомлений
            $table->integer('reminder_hours_before')->default(24); // За сколько часов напоминать
            $table->boolean('email_notifications')->default(false);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            
            // Дополнительная информация
            $table->text('notes')->nullable(); // Личные заметки пользователя
            $table->boolean('is_attending')->default(false); // Планирует ли участвовать
            $table->timestamp('attending_confirmed_at')->nullable();
            
            // Временные метки
            $table->timestamp('subscribed_at');
            $table->timestamp('last_notification_at')->nullable();
            $table->timestamps();
            
            // Индексы
            $table->unique(['user_id', 'event_id']);
            $table->index(['event_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('subscribed_at');
            $table->index('is_attending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_subscriptions');
    }
};