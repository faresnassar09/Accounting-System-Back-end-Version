<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Fixed Assets Registry Table
        if (!Schema::hasTable('fixed_assets')) {
            Schema::create('fixed_assets', function (Blueprint $table) {
                $table->id();
                $table->string('asset_number', 50)->unique();
                $table->string('name', 150);
                $table->enum('category', [
                    'equipment',
                    'vehicles',
                    'furniture',
                    'buildings',
                    'land',
                    'intangible',
                ])->default('equipment');
                $table->date('purchase_date');
                $table->decimal('purchase_cost', 15, 2);
                $table->decimal('salvage_value', 15, 2)->default(0.00);
                $table->unsignedInteger('useful_life_months');
                $table->enum('depreciation_method', ['straight_line', 'declining_balance'])->default('straight_line');

                // General Ledger Account Mappings
                $table->foreignId('asset_account_id')->constrained('accounts');
                $table->foreignId('depreciation_expense_account_id')->constrained('accounts');
                $table->foreignId('accumulated_depreciation_account_id')->constrained('accounts');

                $table->decimal('accumulated_depreciation', 15, 2)->default(0.00);
                $table->decimal('book_value', 15, 2);
                $table->enum('status', ['active', 'fully_depreciated', 'disposed'])->default('active');
                $table->date('disposal_date')->nullable();
                $table->decimal('disposal_amount', 15, 2)->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->timestamps();

                $table->index(['status', 'purchase_date']);
            });
        }

        // 2. Depreciation Schedules & Posting History Table
        if (!Schema::hasTable('depreciation_schedules')) {
            Schema::create('depreciation_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fixed_asset_id')
                    ->constrained('fixed_assets')
                    ->cascadeOnDelete();
                $table->date('period_date');
                $table->decimal('depreciation_amount', 15, 2);
                $table->decimal('accumulated_after', 15, 2);
                $table->decimal('book_value_after', 15, 2);
                $table->foreignId('journal_entry_id')
                    ->nullable()
                    ->constrained('journal_entries')
                    ->nullOnDelete();
                $table->timestamps();

                $table->unique(['fixed_asset_id', 'period_date'], 'asset_period_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('depreciation_schedules');
        Schema::dropIfExists('fixed_assets');
    }
};
