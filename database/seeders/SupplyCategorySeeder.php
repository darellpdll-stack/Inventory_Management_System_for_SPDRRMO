<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SupplyCategory;

class SupplyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $categories = [
        ['name' => 'Medical Supplies', 'description' => 'Medicines, first aid, and medical equipment'],
        ['name' => 'Office Supplies', 'description' => 'Paper, pens, and office materials'],
        ['name' => 'Training Supplies', 'description' => 'Materials used for trainings and drills'],
    ];

    foreach ($categories as $category) {
        SupplyCategory::firstOrCreate(['name' => $category['name']], $category);
    }
}
}
