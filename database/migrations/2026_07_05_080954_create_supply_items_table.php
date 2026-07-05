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
    Schema::create('supply_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->constrained('supply_categories')->onDelete('cascade');
        $table->string('item_name');
        $table->string('unit')->default('pcs');
        $table->integer('current_stock')->default(0);
        $table->integer('minimum_stock')->default(0);
        $table->string('status')->default('active');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    
};
