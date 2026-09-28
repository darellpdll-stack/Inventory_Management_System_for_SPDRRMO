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
    Schema::table('supply_items', function (Blueprint $table) {
        $table->boolean('tracks_expiry')->default(false)->after('expiration_date');
    });
}

};
