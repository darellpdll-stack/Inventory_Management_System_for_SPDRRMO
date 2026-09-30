<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('releases', function (Blueprint $table) {
        $table->id();
        $table->foreignId('supply_request_id')->constrained()->cascadeOnDelete();
        $table->date('date_released');
        $table->string('received_by')->nullable();
        $table->string('remarks')->nullable();
        $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
    });
}

    
};
