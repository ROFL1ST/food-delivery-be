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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            // order id
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            // product id
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            // quantity
            $table->integer('quantity')->default(1);
            // price
            $table->integer('price')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
