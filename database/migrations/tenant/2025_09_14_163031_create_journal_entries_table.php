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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
            ->constrained(); 

            $table->timestamp('date');
            $table->string('reference')
            ->index('refernce');

            $table->string('description',255);
            $table->enum('status',['approved','draft','cancled']);
            $table->enum('type', ['journal', 'opening', 'closing', 'adjustment'])->default('journal');
            $table->decimal('total_debit',15,2);
            $table->decimal('total_credit',15,2);

            
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
