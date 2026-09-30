<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->char('currency', 3)->default('MMK');

            // Money is stored in minor units (integers only — never floats).
            $table->unsignedBigInteger('balance')->default(0);

            $table->boolean('is_frozen')->default(false);
            $table->timestamp('frozen_at')->nullable();
            $table->string('freeze_reason')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'is_frozen']);
        });

        // Defence-in-depth: DB rejects negative balances even if a bug slips past the service.
        // Requires MySQL 8.0.16+ or MariaDB 10.2.2+. Remove this line on older servers.
        DB::statement('ALTER TABLE wallets ADD CONSTRAINT wallets_balance_check CHECK (balance >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
