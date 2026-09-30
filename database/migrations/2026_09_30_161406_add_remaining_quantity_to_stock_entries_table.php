<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_entries', function (Blueprint $table) {
            $table->integer('remaining_quantity')->default(0)->after('quantity');
        });

        // existing deliveries start with everything still in them
        DB::statement('UPDATE stock_entries SET remaining_quantity = quantity');
    }

    
};