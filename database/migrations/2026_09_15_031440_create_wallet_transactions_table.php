<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type');
            $table->enum('direction', ['credit', 'debit']);

            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('balance_before')->nullable();
            $table->unsignedBigInteger('balance_after')->nullable();

            $table->char('currency', 3)->default('MMK');

            $table->string('reference');
            $table->string('idempotency_key')->nullable()->unique();

            $table->string('status')->default('completed');
            $table->string('description')->nullable();

            // Morph columns (explicit — nullableMorphs() creates a 77-char index name)
            $table->string('transactionable_type')->nullable();
            $table->unsignedBigInteger('transactionable_id')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['wallet_id', 'created_at']);
            $table->index(
                ['transactionable_type', 'transactionable_id'],
                'wt_txnable_index'          // short, < 64 chars
            );

            $table->unique(['reference', 'wallet_id', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
