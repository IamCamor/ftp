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
        Schema::create('catch_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catch_id')->constrained('catch_records')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('category', ['spam', 'advertisement', 'fraud'])->comment('Категория жалобы: спам, реклама, обман');
            $table->text('description')->comment('Описание жалобы (до 1000 символов)');
            $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            
            // Индексы
            $table->index(['catch_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('status');
            
            // Уникальный индекс - один пользователь может пожаловаться на один улов только один раз
            $table->unique(['catch_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catch_reports');
    }
};
