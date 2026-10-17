<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physical_count_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('physical_count_id')->constrained()->cascadeOnDelete();
            $table->string('property_no');
            // null when the scanned tag matches no property record at all
            $table->foreignId('property_item_id')->nullable()->constrained('property_items')->nullOnDelete();
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // the same tag cannot be counted twice in one count
            $table->unique(['physical_count_id', 'property_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_count_scans');
    }
};
