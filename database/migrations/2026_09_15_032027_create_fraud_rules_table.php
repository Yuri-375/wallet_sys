<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fraud_rules', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            // transfer_frequency | large_transfer_otp | new_receiver_high_amount | new_account_transfer

            $table->string('name');
            $table->text('description');

            $table->string('configuration_type')->nullable();
            // used by FraudService::ruleMatches()

            $table->unsignedBigInteger('threshold')->nullable();
            $table->json('configuration')->nullable();

            $table->string('severity')->nullable();
            // low | medium | high

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_rules');
    }
};
