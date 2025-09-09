<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 191)->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('password')->nullable();
            $table->string('name', 120);
            $table->string('username', 64)->unique()->nullable();
            $table->string('photo_url', 512)->nullable();
            $table->enum('role', ['user', 'pro', 'premium', 'admin'])->default('user');
            $table->timestamps();

            $table->index('email');
            $table->index('username');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

