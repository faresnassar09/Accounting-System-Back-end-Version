<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('accounting_audit_logs')) {
            Schema::create('accounting_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('auditable_type', 150);
                $table->unsignedBigInteger('auditable_id');
                $table->string('event', 50); // created, updated, reversed, closed, disposed, reconciled
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_type', 50)->nullable();
                $table->string('user_name', 150)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('description')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['auditable_type', 'auditable_id']);
                $table->index('event');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_audit_logs');
    }
};
