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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('merchant');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('RON');
            $table->text('url_affiliate');
            $table->boolean('in_stock')->default(true);
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['product_id', 'merchant']);
            $table->index('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
