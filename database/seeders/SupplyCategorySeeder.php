<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SupplyCategory;

class SupplyCategorySeeder extends Seeder
{
   public function run(): void
{
    $categories = [
        ['name' => 'Office Supplies', 'description' => 'General office materials (OFS)'],
        ['name' => 'Other Supplies', 'description' => 'Janitorial, hardware, and other supplies (JOS)'],
        ['name' => 'Inks, Toners & Computer Accessories', 'description' => 'Inks, toners, and computer items (ITC)'],
        ['name' => 'Medical', 'description' => 'Medicines, first aid, and medical items'],
    ];

    foreach ($categories as $category) {
        \App\Models\SupplyCategory::firstOrCreate(['name' => $category['name']], $category);
    }
}
}
