<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catch_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('species', 120)->nullable();
            $table->decimal('length', 6, 2)->nullable();
            $table->decimal('weight', 6, 2)->nullable();
            $table->string('style', 120)->nullable();
            $table->string('lure', 120)->nullable();
            $table->string('tackle', 120)->nullable();
            $table->text('notes')->nullable();
            $table->string('photo_url', 512)->nullable();
            $table->enum('privacy', ['all', 'friends', 'me'])->default('all');
            $table->datetime('caught_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->spatialIndex(['lat', 'lng']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catch_records');
    }
};

