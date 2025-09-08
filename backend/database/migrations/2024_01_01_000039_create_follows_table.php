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
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade'); // Кто подписывается
            $table->foreignId('following_id')->constrained('users')->onDelete('cascade'); // На кого подписываются
            $table->timestamps();

            // Уникальная пара подписчик-подписка
            $table->unique(['follower_id', 'following_id']);
            
            // Индексы для быстрого поиска
            $table->index(['follower_id']);
            $table->index(['following_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
