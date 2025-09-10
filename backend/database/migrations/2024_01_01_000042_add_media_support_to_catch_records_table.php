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
        Schema::table('catch_records', function (Blueprint $table) {
            $table->json('photos')->nullable()->after('description'); // Массив URL фото
            $table->json('videos')->nullable()->after('photos'); // Массив URL видео
            $table->string('main_photo')->nullable()->after('videos'); // Главное фото для превью
            $table->string('main_video')->nullable()->after('main_photo'); // Главное видео для превью
            $table->integer('media_count')->default(0)->after('main_video'); // Общее количество медиа
            
            $table->index(['media_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catch_records', function (Blueprint $table) {
            $table->dropIndex(['media_count']);
            $table->dropColumn([
                'photos',
                'videos',
                'main_photo',
                'main_video',
                'media_count'
            ]);
        });
    }
};
