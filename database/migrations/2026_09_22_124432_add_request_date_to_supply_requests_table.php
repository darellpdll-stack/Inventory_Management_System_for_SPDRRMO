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
    Schema::table('supply_requests', function (Blueprint $table) {
        $table->date('request_date')->nullable()->after('personnel_id');
    });
}

    
};
