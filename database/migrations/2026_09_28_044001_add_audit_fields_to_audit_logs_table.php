<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {

            if (!Schema::hasColumn('audit_logs', 'description')) {
                $table->text('description')
                    ->nullable()
                    ->after('action');
            }

            if (!Schema::hasColumn('audit_logs', 'auditable_type')) {
                $table->string('auditable_type')
                    ->nullable()
                    ->after('description');
            }

            if (!Schema::hasColumn('audit_logs', 'auditable_id')) {
                $table->unsignedBigInteger('auditable_id')
                    ->nullable()
                    ->after('auditable_type');
            }

            if (!Schema::hasColumn('audit_logs', 'old_values')) {
                $table->json('old_values')
                    ->nullable()
                    ->after('auditable_id');
            }

            if (!Schema::hasColumn('audit_logs', 'new_values')) {
                $table->json('new_values')
                    ->nullable()
                    ->after('old_values');
            }

            if (!Schema::hasColumn('audit_logs', 'ip_address')) {
                $table->ipAddress('ip_address')
                    ->nullable()
                    ->after('new_values');
            }

            if (!Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->text('user_agent')
                    ->nullable()
                    ->after('ip_address');
            }
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(
                ['auditable_type', 'auditable_id'],
                'audit_logs_auditable_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {

            $table->dropIndex(
                'audit_logs_auditable_index'
            );

            $columns = [];

            foreach (
                [
                    'description',
                    'auditable_type',
                    'auditable_id',
                    'old_values',
                    'new_values',
                    'ip_address',
                    'user_agent',
                ] as $column
            ) {
                if (Schema::hasColumn('audit_logs', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
