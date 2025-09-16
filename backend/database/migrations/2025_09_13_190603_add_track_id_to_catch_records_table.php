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
            $table->unsignedBigInteger('track_id')->nullable()->after('user_id')->comment('ID трека, к которому принадлежит улов');
            $table->foreign('track_id')->references('id')->on('tracks')->onDelete('set null');
            $table->index('track_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catch_records', function (Blueprint $table) {
            $table->dropForeign(['track_id']);
            $table->dropIndex(['track_id']);
            $table->dropColumn('track_id');
        });
    }
};