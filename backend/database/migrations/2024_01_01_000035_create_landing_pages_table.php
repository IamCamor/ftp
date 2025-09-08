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
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Заголовок страницы
            $table->string('slug')->unique(); // URL slug
            $table->text('description')->nullable(); // Описание
            $table->longText('content')->nullable(); // HTML контент
            $table->string('meta_title')->nullable(); // SEO заголовок
            $table->text('meta_description')->nullable(); // SEO описание
            $table->json('meta_keywords')->nullable(); // SEO ключевые слова
            $table->string('featured_image')->nullable(); // Главное изображение
            $table->json('gallery')->nullable(); // Галерея изображений
            $table->string('template')->default('default'); // Шаблон страницы
            $table->json('custom_fields')->nullable(); // Дополнительные поля
            $table->string('status')->default('draft'); // draft, published, archived
            $table->boolean('is_featured')->default(false); // Рекомендуемая
            $table->integer('view_count')->default(0); // Количество просмотров
            $table->timestamp('published_at')->nullable(); // Дата публикации
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade'); // Автор
            $table->timestamps();

            $table->index(['slug']);
            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'status']);
            $table->index(['author_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
    }
};
