<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->text('description')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('location_name', 191)->nullable();
            $table->datetime('start_at');
            $table->datetime('end_at')->nullable();
            $table->integer('max_participants')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('cover_url', 512)->nullable();
            $table->timestamps();

            $table->index('organizer_id');
            $table->index('group_id');
            $table->index('start_at');
            $table->index('status');
            $table->spatialIndex(['lat', 'lng']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

