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
        Schema::create('accounting_mappings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('label', 255);
            $table->enum('integration_key', [
                'opening_diff_balance',
                'payment_system_customer',
                'payment_system_provider',
                'platform_fees'
            ]);
            $table->string('description', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_mappings');
    }
};
