<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catch_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catch_id')->constrained('catch_records')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('body');
            $table->boolean('is_approved')->nullable();
            $table->timestamps();

            $table->index('catch_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catch_comments');
    }
};

