<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE supply_requests DROP CONSTRAINT IF EXISTS supply_requests_status_check');
    }

    
};