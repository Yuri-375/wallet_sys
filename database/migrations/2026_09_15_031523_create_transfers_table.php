    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('transfers', function (Blueprint $table) {
                $table->id();

                $table->foreignId('sender_id')->constrained('users');
                $table->foreignId('receiver_id')->constrained('users');

                $table->unsignedBigInteger('amount');
                $table->char('currency', 3)->default('MMK');

                $table->string('reference')->unique();
                $table->string('idempotency_key')->unique();

                $table->string('status')->default('pending');
                // pending | otp_required | completed | failed | reversed

                $table->string('otp_code')->nullable();
                $table->timestamp('otp_expires_at')->nullable();
                $table->unsignedTinyInteger('otp_attempts')->default(0);
                $table->timestamp('otp_verified_at')->nullable();

                $table->text('description')->nullable();

                $table->timestamps();

                $table->index(['sender_id', 'created_at']);
                $table->index(['receiver_id', 'created_at']);
                $table->index('status');
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('transfers');
        }
    };
