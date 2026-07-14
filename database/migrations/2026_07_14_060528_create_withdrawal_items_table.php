<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('withdrawal_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('withdrawal_id')->constrained()->onDelete('cascade');
        $table->foreignId('supply_item_id')->constrained('supply_items');
        $table->integer('quantity');
        $table->timestamps();
    });
}

    
};
