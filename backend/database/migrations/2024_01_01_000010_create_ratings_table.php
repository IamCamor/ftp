<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->enum('entity_type', ['catch', 'point', 'user']);
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('value')->unsigned();
            $table->timestamps();

            $table->unique(['entity_type', 'entity_id', 'user_id']);
            $table->index(['entity_type', 'entity_id']);
            // Note: CHECK constraint removed for SQLite compatibility
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};

