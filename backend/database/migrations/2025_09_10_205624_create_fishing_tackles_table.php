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
        Schema::create('fishing_tackles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->string('category')->nullable();
            $table->json('specifications')->nullable();
            $table->json('features')->nullable();
            $table->decimal('price_min', 10, 2)->nullable();
            $table->decimal('price_max', 10, 2)->nullable();
            $table->string('photo_url')->nullable();
            $table->json('additional_photos')->nullable();
            $table->json('pros')->nullable();
            $table->json('cons')->nullable();
            $table->json('best_for')->nullable();
            $table->json('compatible_with')->nullable();
            $table->json('maintenance')->nullable();
            $table->integer('view_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishing_tackles');
    }
};
