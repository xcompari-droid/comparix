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
        Schema::create('spec_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('spec_key_id')->constrained()->onDelete('cascade');
            $table->text('value_string')->nullable();
            $table->decimal('value_number', 20, 6)->nullable();
            $table->boolean('value_bool')->nullable();
            $table->timestamps();
            
            $table->index(['product_id', 'spec_key_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spec_values');
    }
};
