<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Recurring Journal Entries Master Table
        if (!Schema::hasTable('recurring_journal_entries')) {
            Schema::create('recurring_journal_entries', function (Blueprint $table) {
                $table->id();
                $table->string('reference_template', 100);
                $table->string('description', 255);
                $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->date('last_run_date')->nullable();
                $table->date('next_run_date');
                $table->enum('status', ['active', 'paused', 'completed'])->default('active');
                $table->string('currency_code', 3)->default('USD');
                $table->decimal('exchange_rate', 15, 6)->default(1.000000);
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->boolean('auto_post')->default(true);
                $table->decimal('total_debit', 15, 2)->default(0.00);
                $table->decimal('total_credit', 15, 2)->default(0.00);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['status', 'next_run_date'], 'recurring_status_next_run_idx');
            });
        }

        // 2. Recurring Journal Entry Line Items
        if (!Schema::hasTable('recurring_journal_entry_lines')) {
            Schema::create('recurring_journal_entry_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('recurring_journal_entry_id')
                    ->constrained('recurring_journal_entries')
                    ->cascadeOnDelete();
                $table->foreignId('account_id')
                    ->constrained('accounts');
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->decimal('debit', 15, 2)->default(0.00);
                $table->decimal('credit', 15, 2)->default(0.00);
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }

        // 3. Link Journal Entries back to parent recurring template
        Schema::table('journal_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_entries', 'recurring_journal_entry_id')) {
                $table->unsignedBigInteger('recurring_journal_entry_id')->nullable()->after('branch_id');
                $table->foreign('recurring_journal_entry_id')
                    ->references('id')
                    ->on('recurring_journal_entries')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entries', 'recurring_journal_entry_id')) {
                $table->dropForeign(['recurring_journal_entry_id']);
                $table->dropColumn('recurring_journal_entry_id');
            }
        });

        Schema::dropIfExists('recurring_journal_entry_lines');
        Schema::dropIfExists('recurring_journal_entries');
    }
};
