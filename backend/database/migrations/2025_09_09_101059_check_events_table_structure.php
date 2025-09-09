<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check existing columns
        $columns = Schema::getColumnListing('events');
        echo "Existing columns in events table: " . implode(', ', $columns) . "\n";
        
        // Add only missing columns
        Schema::table('events', function (Blueprint $table) use ($columns) {
            if (!in_array('type', $columns)) {
                $table->string('type')->default('exhibition');
            }
            if (!in_array('organizer', $columns)) {
                $table->string('organizer')->nullable();
            }
            if (!in_array('contact_email', $columns)) {
                $table->string('contact_email')->nullable();
            }
            if (!in_array('contact_phone', $columns)) {
                $table->string('contact_phone')->nullable();
            }
            if (!in_array('website', $columns)) {
                $table->string('website')->nullable();
            }
            if (!in_array('address', $columns)) {
                $table->string('address')->nullable();
            }
            if (!in_array('city', $columns)) {
                $table->string('city')->nullable();
            }
            if (!in_array('region', $columns)) {
                $table->string('region')->nullable();
            }
            if (!in_array('country', $columns)) {
                $table->string('country')->nullable();
            }
            if (!in_array('latitude', $columns)) {
                $table->decimal('latitude', 10, 7)->nullable();
            }
            if (!in_array('longitude', $columns)) {
                $table->decimal('longitude', 11, 7)->nullable();
            }
            if (!in_array('radius_km', $columns)) {
                $table->integer('radius_km')->default(50);
            }
            if (!in_array('registration_start', $columns)) {
                $table->timestamp('registration_start')->nullable();
            }
            if (!in_array('registration_end', $columns)) {
                $table->timestamp('registration_end')->nullable();
            }
            if (!in_array('event_start', $columns)) {
                $table->timestamp('event_start')->nullable();
            }
            if (!in_array('event_end', $columns)) {
                $table->timestamp('event_end')->nullable();
            }
            if (!in_array('is_all_day', $columns)) {
                $table->boolean('is_all_day')->default(false);
            }
            if (!in_array('current_participants', $columns)) {
                $table->integer('current_participants')->default(0);
            }
            if (!in_array('entry_fee', $columns)) {
                $table->decimal('entry_fee', 10, 2)->nullable();
            }
            if (!in_array('currency', $columns)) {
                $table->string('currency', 3)->default('RUB');
            }
            if (!in_array('requires_registration', $columns)) {
                $table->boolean('requires_registration')->default(false);
            }
            if (!in_array('is_public', $columns)) {
                $table->boolean('is_public')->default(true);
            }
            if (!in_array('moderation_status', $columns)) {
                $table->enum('moderation_status', ['pending', 'approved', 'rejected', 'pending_review'])->default('pending');
            }
            if (!in_array('moderation_result', $columns)) {
                $table->json('moderation_result')->nullable();
            }
            if (!in_array('moderated_at', $columns)) {
                $table->timestamp('moderated_at')->nullable();
            }
            if (!in_array('moderated_by', $columns)) {
                $table->unsignedBigInteger('moderated_by')->nullable();
            }
            if (!in_array('cover_image', $columns)) {
                $table->string('cover_image')->nullable();
            }
            if (!in_array('gallery', $columns)) {
                $table->json('gallery')->nullable();
            }
            if (!in_array('documents', $columns)) {
                $table->json('documents')->nullable();
            }
            if (!in_array('rules', $columns)) {
                $table->text('rules')->nullable();
            }
            if (!in_array('prizes', $columns)) {
                $table->text('prizes')->nullable();
            }
            if (!in_array('schedule', $columns)) {
                $table->text('schedule')->nullable();
            }
            if (!in_array('views_count', $columns)) {
                $table->integer('views_count')->default(0);
            }
            if (!in_array('subscribers_count', $columns)) {
                $table->integer('subscribers_count')->default(0);
            }
            if (!in_array('shares_count', $columns)) {
                $table->integer('shares_count')->default(0);
            }
            if (!in_array('rating', $columns)) {
                $table->decimal('rating', 3, 2)->nullable();
            }
            if (!in_array('reviews_count', $columns)) {
                $table->integer('reviews_count')->default(0);
            }
            if (!in_array('notifications_enabled', $columns)) {
                $table->boolean('notifications_enabled')->default(true);
            }
            if (!in_array('reminders_enabled', $columns)) {
                $table->boolean('reminders_enabled')->default(true);
            }
            if (!in_array('allow_comments', $columns)) {
                $table->boolean('allow_comments')->default(true);
            }
            if (!in_array('allow_sharing', $columns)) {
                $table->boolean('allow_sharing')->default(true);
            }
            if (!in_array('tags', $columns)) {
                $table->json('tags')->nullable();
            }
            if (!in_array('categories', $columns)) {
                $table->json('categories')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only adds columns, so we don't need to remove them
    }
};