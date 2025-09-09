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
        Schema::create('event_news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Автор новости
            
            // Содержимое новости
            $table->string('title');
            $table->text('content');
            $table->text('excerpt')->nullable(); // Краткое описание
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable(); // Массив изображений
            $table->json('attachments')->nullable(); // Массив вложений
            
            // Тип новости
            $table->enum('type', ['announcement', 'update', 'reminder', 'result', 'photo_report', 'other'])->default('announcement');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Статус и модерация
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->enum('moderation_status', ['pending', 'approved', 'rejected', 'pending_review'])->default('pending');
            $table->json('moderation_result')->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->unsignedBigInteger('moderated_by')->nullable();
            
            // Публикация
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable(); // Запланированная публикация
            $table->boolean('is_pinned')->default(false); // Закрепленная новость
            
            // Статистика
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->integer('comments_count')->default(0);
            
            // Настройки
            $table->boolean('allow_comments')->default(true);
            $table->boolean('allow_sharing')->default(true);
            $table->boolean('send_notifications')->default(true);
            $table->json('tags')->nullable();
            
            $table->timestamps();
            
            // Индексы
            $table->index(['event_id', 'status']);
            $table->index(['event_id', 'published_at']);
            $table->index(['user_id', 'status']);
            $table->index('type');
            $table->index('priority');
            $table->index('is_pinned');
            $table->index('moderation_status');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_news');
    }
};