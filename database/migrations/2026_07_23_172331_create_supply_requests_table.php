<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('supply_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('personnel_id')->constrained('personnel');
        $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
        $table->string('purpose')->nullable();
        $table->string('decline_reason')->nullable();
        $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('reviewed_at')->nullable();
        $table->foreignId('withdrawal_id')->nullable()->constrained('withdrawals')->nullOnDelete();
        $table->timestamps();
    });
}
};
