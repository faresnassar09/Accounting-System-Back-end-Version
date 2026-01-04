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
        Schema::table('activity_log', function (Blueprint $table) {


            $table->foreignId('company_id')
            ->constrained()
            ->cascadeOnDelete();

            $table->foreignId('branch_id')
            ->constrained()
            ->cascadeOnDelete();
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activitylog_tabe', function (Blueprint $table) {
            //
        });
    }
};
