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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('parent_id')
            ->nullable()
            ->constrained('accounts','id')
            ->cascadeOnDelete();   

            $table->foreignId('account_type_id')
            ->nullable()
            ->constrained()
            ->cascadeOnDelete();  
            
            $table->string('name',50);
            $table->string('number');

            $table->decimal('initial_balance',15,2)->nullable();
            $table->string('description',180)->nullable();

            $table->boolean('active')
            ->default(1)
            ->comment(" 1 => Active  0 => Not Active");  

            $table->decimal('calculated_balance',15,2)->nullable();

            $table->decimal('descendants_count',15,2)->nullable();
            
            $table->timestamps();



            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
