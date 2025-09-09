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
            $table->boolean('is_premium')->default(false)->after('role');
            $table->timestamp('premium_expires_at')->nullable()->after('is_premium');
            $table->string('crown_icon_url')->nullable()->after('premium_expires_at');
            $table->integer('bonus_balance')->default(0)->after('crown_icon_url');
            $table->timestamp('last_bonus_earned_at')->nullable()->after('bonus_balance');
            
            $table->index(['is_premium']);
            $table->index(['premium_expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_premium']);
            $table->dropIndex(['premium_expires_at']);
            $table->dropColumn([
                'is_premium',
                'premium_expires_at',
                'crown_icon_url',
                'bonus_balance',
                'last_bonus_earned_at'
            ]);
        });
    }
};
