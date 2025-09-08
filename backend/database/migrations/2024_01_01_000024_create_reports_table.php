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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reporter_id');
            $table->foreign('reporter_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->string('reportable_type'); // 'App\Models\CatchRecord', 'App\Models\Point', 'App\Models\User'
            $table->unsignedBigInteger('reportable_id');
            $table->index(['reportable_type', 'reportable_id']);
            
            $table->enum('reason', [
                'spam', 'inappropriate_content', 'fake_content', 'harassment', 
                'violence', 'illegal_content', 'copyright_violation', 'other'
            ]);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending');
            
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
