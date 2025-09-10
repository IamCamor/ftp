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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('followers_count')->default(0)->after('bonus_balance');
            $table->integer('following_count')->default(0)->after('followers_count');
            $table->integer('total_likes_received')->default(0)->after('following_count');
            $table->timestamp('last_seen_at')->nullable()->after('total_likes_received');
            $table->boolean('is_online')->default(false)->after('last_seen_at');
            
            $table->index(['is_online', 'last_seen_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_online', 'last_seen_at']);
            $table->dropColumn([
                'followers_count',
                'following_count', 
                'total_likes_received',
                'last_seen_at',
                'is_online'
            ]);
        });
    }
};
