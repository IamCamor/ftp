<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('title', 191);
            $table->text('description')->nullable();
            $table->string('cover_url', 512)->nullable();
            $table->enum('privacy', ['all', 'friends', 'me'])->default('all');
            $table->timestamps();

            $table->spatialIndex(['lat', 'lng']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points');
    }
};

