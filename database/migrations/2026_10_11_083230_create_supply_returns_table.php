<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('supply_returns', function (Blueprint $table) {
        $table->id();
        $table->foreignId('release_item_id')->constrained()->cascadeOnDelete();
        $table->foreignId('supply_item_id')->constrained('supply_items');
        $table->integer('quantity');
        $table->date('date_returned');
        $table->string('returned_by')->nullable();
        $table->string('condition')->default('usable');
        $table->foreignId('stock_entry_id')->nullable()->constrained('stock_entries')->nullOnDelete();
        $table->string('remarks')->nullable();
        $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
    });
}
};
