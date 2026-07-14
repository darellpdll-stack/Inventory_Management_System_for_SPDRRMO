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
        ['name' => 'Ink & Toners', 'description' => 'Printer inks and toner cartridges'],
        ['name' => 'Computer Accessories', 'description' => 'Peripherals and computer parts'],
        ['name' => 'Medical', 'description' => 'Medicines, first aid, and medical items'],
        ['name' => 'Office', 'description' => 'Paper, pens, and office materials'],
    ];

    foreach ($categories as $category) {
        SupplyCategory::firstOrCreate(['name' => $category['name']], $category);
    }
}
}
