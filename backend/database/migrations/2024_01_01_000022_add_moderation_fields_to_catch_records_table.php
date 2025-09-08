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
            $table->boolean('is_blocked')->default(false)->after('is_public');
            $table->timestamp('blocked_at')->nullable()->after('is_blocked');
            $table->text('block_reason')->nullable()->after('blocked_at');
            $table->unsignedBigInteger('blocked_by')->nullable()->after('block_reason');
            $table->foreign('blocked_by')->references('id')->on('users')->onDelete('set null');
            $table->boolean('is_edited_by_admin')->default(false)->after('blocked_by');
            $table->timestamp('edited_by_admin_at')->nullable()->after('is_edited_by_admin');
            $table->unsignedBigInteger('edited_by_admin_id')->nullable()->after('edited_by_admin_at');
            $table->foreign('edited_by_admin_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catch_records', function (Blueprint $table) {
            $table->dropForeign(['blocked_by']);
            $table->dropForeign(['edited_by_admin_id']);
            $table->dropColumn([
                'is_blocked', 'blocked_at', 'block_reason', 'blocked_by',
                'is_edited_by_admin', 'edited_by_admin_at', 'edited_by_admin_id'
            ]);
        });
    }
};
