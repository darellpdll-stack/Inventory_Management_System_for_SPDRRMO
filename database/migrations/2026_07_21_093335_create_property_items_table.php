<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('property_items', function (Blueprint $table) {
        $table->id();
        $table->enum('type', ['semi-expendable', 'expendable']);
        $table->foreignId('category_id')->constrained('property_categories');
        $table->foreignId('personnel_id')->nullable()->constrained('personnel')->nullOnDelete(); // accountable person
        $table->string('description');
        $table->string('property_no')->nullable();
        $table->string('unit')->default('unit');
        $table->decimal('unit_value', 12, 2)->nullable();
        $table->integer('on_hand_per_count')->default(1);
        $table->string('remarks')->nullable();
        $table->timestamps();
    });
}

    
};
