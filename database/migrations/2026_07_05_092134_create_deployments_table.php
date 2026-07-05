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
    Schema::create('deployments', function (Blueprint $table) {
        $table->id();
        $table->string('destination');                 // e.g. Brgy. Rizal, Sorsogon
        $table->string('purpose')->nullable();         // e.g. Typhoon response
        $table->string('authorized_by');               // name of officer who approved
        $table->foreignId('released_by')->constrained('users'); // logged-in staff
        $table->string('status')->default('deployed'); // deployed / returned / consumed
        $table->date('deployed_at');
        $table->timestamps();
    });
}
    
};
