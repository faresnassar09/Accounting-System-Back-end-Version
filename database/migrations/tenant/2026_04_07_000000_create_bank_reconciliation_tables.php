<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bank Statements Header Table
        if (!Schema::hasTable('bank_statements')) {
            Schema::create('bank_statements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_id')->constrained('accounts');
                $table->date('statement_date');
                $table->string('filename', 255)->nullable();
                $table->decimal('opening_balance', 15, 2)->default(0.00);
                $table->decimal('closing_balance', 15, 2)->default(0.00);
                $table->decimal('reconciled_balance', 15, 2)->default(0.00);
                $table->enum('status', ['draft', 'reconciling', 'reconciled'])->default('draft');
                $table->timestamps();
            });
        }

        // 2. Bank Statement Lines Table
        if (!Schema::hasTable('bank_statement_lines')) {
            Schema::create('bank_statement_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bank_statement_id')
                    ->constrained('bank_statements')
                    ->cascadeOnDelete();
                $table->date('date');
                $table->string('description', 255);
                $table->string('reference', 100)->nullable();
                $table->decimal('amount', 15, 2);
                $table->foreignId('matched_journal_entry_line_id')
                    ->nullable()
                    ->constrained('journal_entry_lines')
                    ->nullOnDelete();
                $table->enum('status', ['unmatched', 'matched', 'created_adjustment'])->default('unmatched');
                $table->timestamps();
            });
        }

        // 3. Mark Journal Entry Lines with reconciliation flag
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_entry_lines', 'is_reconciled')) {
                $table->boolean('is_reconciled')->default(false)->after('date');
                $table->timestamp('reconciled_at')->nullable()->after('is_reconciled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entry_lines', 'is_reconciled')) {
                $table->dropColumn(['is_reconciled', 'reconciled_at']);
            }
        });

        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('bank_statements');
    }
};
