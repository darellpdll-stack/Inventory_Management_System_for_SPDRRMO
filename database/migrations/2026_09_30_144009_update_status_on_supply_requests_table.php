<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    // enum -> string so we can add for_release and completed
    DB::statement("ALTER TABLE supply_requests ALTER COLUMN status TYPE VARCHAR(20)");
    DB::statement("ALTER TABLE supply_requests ALTER COLUMN status SET DEFAULT 'pending'");
}

   
};
