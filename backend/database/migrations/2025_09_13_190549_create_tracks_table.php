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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title')->nullable()->comment('Название трека');
            $table->text('description')->nullable()->comment('Описание трека');
            $table->datetime('started_at')->comment('Время начала трека');
            $table->datetime('ended_at')->nullable()->comment('Время окончания трека');
            $table->enum('status', ['active', 'paused', 'completed'])->default('active')->comment('Статус трека');
            $table->json('smartwatch_data')->nullable()->comment('Данные с умных часов');
            $table->json('track_points')->nullable()->comment('Точки трека для отображения на карте');
            $table->decimal('total_distance', 10, 2)->nullable()->comment('Общая дистанция в км');
            $table->integer('total_catches')->default(0)->comment('Общее количество уловов');
            $table->decimal('total_weight', 8, 2)->nullable()->comment('Общий вес уловов');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'status']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};