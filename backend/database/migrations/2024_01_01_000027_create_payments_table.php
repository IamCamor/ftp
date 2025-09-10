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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->string('payment_id')->unique(); // ID платежа от платежной системы
            $table->enum('provider', ['yandex_pay', 'sber_pay', 'apple_pay', 'google_pay', 'bonuses']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->enum('type', ['subscription_pro', 'subscription_premium', 'bonus_purchase'])->index();
            $table->decimal('amount', 10, 2); // Сумма в рублях
            $table->string('currency', 3)->default('RUB');
            $table->integer('bonus_amount')->nullable(); // Сумма в бонусах
            $table->text('description')->nullable();
            $table->json('provider_data')->nullable(); // Данные от платежной системы
            $table->json('metadata')->nullable(); // Дополнительные данные
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['provider', 'status']);
            $table->index(['type', 'status']);
            $table->index(['payment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
