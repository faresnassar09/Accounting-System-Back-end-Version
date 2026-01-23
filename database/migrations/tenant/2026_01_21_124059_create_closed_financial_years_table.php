<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('closed_financial_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('closed_by')
            ->constrained('users')
            ->cascadeOnDelete();

            $table->foreignId('retained_earnings_account_id')
            ->constrained('accounts');     
             
            $table->year('year',50)->unique();
            $table->decimal('net_profit_loss', 15, 2);

             
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closed_financial_years');
    }
};
