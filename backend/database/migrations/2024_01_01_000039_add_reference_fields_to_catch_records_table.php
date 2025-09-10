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
        Schema::table('catch_records', function (Blueprint $table) {
            $table->foreignId('fish_species_id')->nullable()->constrained('fish_species')->onDelete('set null');
            $table->foreignId('fishing_method_id')->nullable()->constrained('fishing_methods')->onDelete('set null');
            $table->foreignId('fishing_knot_id')->nullable()->constrained('fishing_knots')->onDelete('set null');
            $table->foreignId('boat_id')->nullable()->constrained('boats')->onDelete('set null');
            $table->foreignId('engine_id')->nullable()->constrained('boat_engines')->onDelete('set null');
            $table->foreignId('location_id')->nullable()->constrained('fishing_locations')->onDelete('set null');
            $table->json('tackle_used')->nullable(); // Использованные снасти (массив ID)
            
            $table->index(['fish_species_id']);
            $table->index(['fishing_method_id']);
            $table->index(['location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catch_records', function (Blueprint $table) {
            $table->dropForeign(['fish_species_id']);
            $table->dropForeign(['fishing_method_id']);
            $table->dropForeign(['fishing_knot_id']);
            $table->dropForeign(['boat_id']);
            $table->dropForeign(['engine_id']);
            $table->dropForeign(['location_id']);
            $table->dropIndex(['fish_species_id']);
            $table->dropIndex(['fishing_method_id']);
            $table->dropIndex(['location_id']);
            $table->dropColumn([
                'fish_species_id',
                'fishing_method_id',
                'fishing_knot_id',
                'boat_id',
                'engine_id',
                'location_id',
                'tackle_used'
            ]);
        });
    }
};
