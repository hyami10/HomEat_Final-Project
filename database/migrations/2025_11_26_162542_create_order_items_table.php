<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->foreignId('food_id')->nullable()->constrained('foods')->onDelete('set null'); 
                $table->string('food_name'); 
                $table->decimal('food_price', 12, 2)->unsigned(); 
                $table->unsignedInteger('quantity');
                $table->decimal('subtotal', 12, 2)->unsigned();
                $table->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
