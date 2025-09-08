<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191)->nullable();
            $table->enum('type', ['private', 'group', 'event'])->default('private');
            $table->foreignId('group_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained('events')->onDelete('cascade');
            $table->timestamps();

            $table->index('type');
            $table->index('group_id');
            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};

