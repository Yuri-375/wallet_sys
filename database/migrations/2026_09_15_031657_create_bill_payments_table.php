<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained();
            $table->foreignId('wallet_id')->constrained();
            $table->foreignId('biller_id')->constrained();

            $table->unsignedBigInteger('amount');
            $table->string('account_number');

            $table->string('reference')->unique();
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('external_reference')->nullable();

            $table->string('status')->default('pending');
            // pending | processing | completed | failed | refunded

            $table->text('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Raw provider response (kept for reconciliation/debugging)
            $table->json('response')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
    }
};
