<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyCategorySeeder extends Seeder
{
    public function run(): void
{
    $categories = [
        ['name' => 'Information Technology', 'code' => 'IT'],
        ['name' => 'Office Supplies', 'code' => 'OFFICE'],
        ['name' => 'Other Supplies', 'code' => 'OS'],
        ['name' => 'Medical, Dental & Laboratory', 'code' => 'MDLS'],
        ['name' => 'Furniture & Fixtures', 'code' => 'F&F'],
        ['name' => 'DRR Equipment & Supplies', 'code' => 'DRRES'],
        ['name' => 'Communications Equipment', 'code' => 'CES'],
        ['name' => 'Other Machinery & Equipment', 'code' => 'OM&E'],
        ['name' => 'Office Automation', 'code' => 'OA'],
        ['name' => 'Other Equipment', 'code' => 'OE'],
    ];

    foreach ($categories as $c) {
        \App\Models\PropertyCategory::firstOrCreate(['code' => $c['code']], $c);
    }
}
}
