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
        Schema::create('bonus_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'earned', 'spent', 'refund'
            $table->string('action'); // 'friend_added', 'catch_recorded', 'point_created', 'comment_added', 'like_given', 'subscription_purchased'
            $table->integer('amount'); // Positive for earned, negative for spent
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Additional data like related entity IDs
            $table->foreignId('related_user_id')->nullable()->constrained('users')->onDelete('set null'); // For friend actions
            $table->foreignId('related_catch_id')->nullable()->constrained('catch_records')->onDelete('set null'); // For catch actions
            $table->foreignId('related_point_id')->nullable()->constrained('points')->onDelete('set null'); // For point actions
            $table->foreignId('related_comment_id')->nullable()->constrained('catch_comments')->onDelete('set null'); // For comment actions
            $table->foreignId('related_like_id')->nullable()->constrained('catch_likes')->onDelete('set null'); // For like actions
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'action']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_transactions');
    }
};