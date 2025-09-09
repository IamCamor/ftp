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
            $table->enum('moderation_status', ['pending', 'approved', 'rejected', 'pending_review'])
                  ->default('pending')
                  ->after('is_public');
            $table->json('moderation_result')->nullable()->after('moderation_status');
            $table->timestamp('moderated_at')->nullable()->after('moderation_result');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->onDelete('set null')->after('moderated_at');
            
            $table->index('moderation_status');
            $table->index('moderated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catch_records', function (Blueprint $table) {
            $table->dropIndex(['moderation_status']);
            $table->dropIndex(['moderated_at']);
            $table->dropForeign(['moderated_by']);
            $table->dropColumn([
                'moderation_status',
                'moderation_result',
                'moderated_at',
                'moderated_by'
            ]);
        });
    }
};