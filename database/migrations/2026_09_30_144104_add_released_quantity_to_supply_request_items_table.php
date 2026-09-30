<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('supply_request_items', function (Blueprint $table) {
        $table->integer('released_quantity')->default(0)->after('quantity');
    });
}

    
};
