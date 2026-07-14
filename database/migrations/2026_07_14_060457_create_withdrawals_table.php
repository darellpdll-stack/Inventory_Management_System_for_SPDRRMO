<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('withdrawals', function (Blueprint $table) {
        $table->id();
        $table->string('withdrawn_by');              // free-text name
        $table->date('date_withdrawn');
        $table->date('date_returned')->nullable();   // optional
        $table->string('remark')->nullable();
        $table->foreignId('recorded_by')->constrained('users'); // who logged it
        $table->timestamps();
    });
}

    
};
