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
        Schema::table('events', function (Blueprint $table) {
            // Основная информация о мероприятии
            $table->string('type')->default('exhibition'); // exhibition, competition, workshop, meeting
            $table->string('organizer')->nullable(); // Организатор
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('website')->nullable();
            
            // Местоположение
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();
            $table->integer('radius_km')->default(50); // Радиус уведомлений в км
            
            // Временные рамки
            $table->timestamp('registration_start')->nullable();
            $table->timestamp('registration_end')->nullable();
            $table->timestamp('event_start');
            $table->timestamp('event_end');
            $table->boolean('is_all_day')->default(false);
            
            // Участие и ограничения
            $table->integer('current_participants')->default(0);
            $table->decimal('entry_fee', 10, 2)->nullable();
            $table->string('currency', 3)->default('RUB');
            $table->boolean('requires_registration')->default(false);
            $table->boolean('is_public')->default(true);
            
            // Модерация
            $table->enum('moderation_status', ['pending', 'approved', 'rejected', 'pending_review'])->default('pending');
            $table->json('moderation_result')->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->unsignedBigInteger('moderated_by')->nullable();
            
            // Медиа и контент
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable(); // Массив изображений
            $table->json('documents')->nullable(); // Массив документов
            $table->text('rules')->nullable(); // Правила участия
            $table->text('prizes')->nullable(); // Призы и награды
            $table->text('schedule')->nullable(); // Программа мероприятия
            
            // Статистика
            $table->integer('views_count')->default(0);
            $table->integer('subscribers_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('reviews_count')->default(0);
            
            // Дополнительные настройки
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('reminders_enabled')->default(true);
            $table->boolean('allow_comments')->default(true);
            $table->boolean('allow_sharing')->default(true);
            $table->json('tags')->nullable(); // Теги для поиска
            $table->json('categories')->nullable(); // Категории
            
            // Индексы
            $table->index(['type', 'status']);
            $table->index(['event_start', 'event_end']);
            $table->index(['latitude', 'longitude']);
            $table->index(['city', 'region']);
            $table->index('moderation_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'type', 'organizer', 'contact_email', 'contact_phone', 'website',
                'address', 'city', 'region', 'country', 'latitude', 'longitude', 'radius_km',
                'registration_start', 'registration_end', 'event_start', 'event_end', 'is_all_day',
                'current_participants', 'entry_fee', 'currency', 'requires_registration', 'is_public',
                'moderation_status', 'moderation_result', 'moderated_at', 'moderated_by',
                'cover_image', 'gallery', 'documents', 'rules', 'prizes', 'schedule',
                'views_count', 'subscribers_count', 'shares_count', 'rating', 'reviews_count',
                'notifications_enabled', 'reminders_enabled', 'allow_comments', 'allow_sharing', 'tags', 'categories'
            ]);
        });
    }
};