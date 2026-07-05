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
    Schema::create('expiry_dismissals', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('supply_item_id')->constrained('supply_items')->onDelete('cascade');
        $table->timestamps();
        $table->unique(['user_id', 'supply_item_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    
};
