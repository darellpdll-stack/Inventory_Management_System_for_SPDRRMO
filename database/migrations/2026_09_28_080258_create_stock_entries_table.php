<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('stock_entries', function (Blueprint $table) {
        $table->id();
        $table->foreignId('supply_item_id')->constrained()->cascadeOnDelete();
        $table->date('date_received');
        $table->string('delivered_by')->nullable();
        $table->string('reference_no')->nullable();
        $table->integer('quantity');
        $table->date('expiration_date')->nullable();
        $table->string('remarks')->nullable();
        $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
    });
}



    
};
