<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('point_id')->constrained()->onDelete('cascade');
            $table->string('url', 512);
            $table->timestamps();

            $table->index('point_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_media');
    }
};

