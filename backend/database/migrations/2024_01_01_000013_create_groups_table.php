<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->string('cover_url', 512)->nullable();
            $table->enum('privacy', ['public', 'private', 'closed'])->default('public');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->integer('members_count')->default(0);
            $table->timestamps();

            $table->index('owner_id');
            $table->index('privacy');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};

