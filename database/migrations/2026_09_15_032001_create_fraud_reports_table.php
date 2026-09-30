<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fraud_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('wallet_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->unsignedBigInteger('fraud_rule_id')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();

            $table->string('severity')->default('medium');
            // low | medium | high

            $table->string('status')->default('open');
            // open | investigating | resolved | dismissed

            $table->text('description');
            $table->json('metadata')->nullable();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();

            $table->timestamps();

            $table->index(['status', 'severity']);
            $table->index(['fraud_rule_id', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_reports');
    }
};
