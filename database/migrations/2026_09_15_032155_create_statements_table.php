<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('wallet_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('period_start');
            $table->date('period_end');

            $table->string('pdf_path')->nullable();

            $table->unsignedBigInteger('opening_balance');
            $table->unsignedBigInteger('total_credits')->default(0);
            $table->unsignedBigInteger('total_debits')->default(0);
            $table->unsignedBigInteger('closing_balance');

            $table->string('status')->default('pending');
            // pending | generated | failed

            $table->timestamp('generated_at')->nullable();

            $table->timestamps();

            $table->unique(['wallet_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statements');
    }
};
