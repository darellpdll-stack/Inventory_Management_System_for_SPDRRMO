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
    // Drop dependent tables from the old structure first
    Schema::dropIfExists('deployment_items');
    Schema::dropIfExists('deployments');
    Schema::dropIfExists('expiry_dismissals');
    Schema::dropIfExists('item_components'); // from the shelved bundle idea, if it exists

    Schema::dropIfExists('supply_items');

    Schema::create('supply_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->constrained('supply_categories')->onDelete('cascade');
        $table->string('product_code');
        $table->string('stock_no')->nullable();
        $table->string('description');
        $table->string('unit')->default('pc');
        $table->decimal('unit_value', 10, 2)->default(0);
        $table->integer('balance_per_card')->default(0);
        $table->integer('on_hand_per_count')->nullable();
        $table->integer('minimum_stock')->default(0);
        $table->date('expiration_date')->nullable();
        $table->string('remarks')->nullable();
        $table->timestamps();
    });

    // Recreate expiry_dismissals linked to the new supply_items
    Schema::create('expiry_dismissals', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('supply_item_id')->constrained('supply_items')->onDelete('cascade');
        $table->timestamps();
        $table->unique(['user_id', 'supply_item_id']);
    });
}

};
