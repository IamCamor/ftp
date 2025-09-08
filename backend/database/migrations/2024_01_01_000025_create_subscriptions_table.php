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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['pro', 'premium'])->index();
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active')->index();
            $table->enum('payment_method', ['yandex_pay', 'sber_pay', 'apple_pay', 'google_pay', 'bonuses'])->nullable();
            $table->decimal('amount', 10, 2)->nullable(); // Сумма в рублях
            $table->integer('bonus_amount')->nullable(); // Сумма в бонусах
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->json('metadata')->nullable(); // Дополнительные данные
            $table->timestamps();

            $table->index(['user_id', 'type', 'status']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
