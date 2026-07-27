<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = [
            'officer_name'  => Setting::get('officer_name', 'CECILIA V. HAINTO'),
            'officer_title' => Setting::get('officer_title', 'Supervising Administrative Officer (Administrative Officer IV)'),
            'office_name'   => Setting::get('office_name', 'SORSOGON PROVINCIAL DISASTER RISK REDUCTION AND MANAGEMENT OFFICE'),
            'property_prepared_name'  => Setting::get('property_prepared_name', 'CECILIA V. HAINTO'),
            'property_prepared_title' => Setting::get('property_prepared_title', 'Supervising Administrative Officer'),
            'property_approved_name'  => Setting::get('property_approved_name', 'RADEN D. DIMAANO, C.E.'),
            'property_approved_title' => Setting::get('property_approved_title', 'PGDH-PDRRMO'),
        ];
        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'officer_name'  => 'required|string|max:255',
            'officer_title' => 'required|string|max:255',
            'office_name'   => 'required|string|max:255',
            'property_prepared_name'  => 'required|string|max:255',
            'property_prepared_title' => 'required|string|max:255',
            'property_approved_name'  => 'required|string|max:255',
            'property_approved_title' => 'required|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Report settings updated.');
    }
}