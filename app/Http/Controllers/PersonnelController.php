<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PersonnelController extends Controller
{
    public function index(Request $request)
    {
        $query = Personnel::query();

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        $personnel = $query->orderBy('name')->paginate(12)->withQueryString();

        // for the filter dropdown — distinct departments already in use
        $departments = Personnel::whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('personnel.index', compact('personnel', 'departments'));
    }

    public function create()
    {
        return view('personnel.create');
    }

    public function store(Request $request)
    {
        set_time_limit(120);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'employee_id' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048', // max 2MB
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('personnel-photos', 'public');
        }

        Personnel::create($validated);

        return redirect()->route('personnel.index')->with('success', 'Personnel added.');
    }

    public function show(Personnel $person)
    {
        return view('personnel.show', compact('person'));
    }

    public function edit(Personnel $person)
    {
        return view('personnel.edit', compact('person'));
    }

    public function update(Request $request, Personnel $person)
    {
        set_time_limit(120);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'employee_id' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            // delete old photo if it exists
            if ($person->photo) {
                Storage::disk('public')->delete($person->photo);
            }
            $validated['photo'] = $request->file('photo')->store('personnel-photos', 'public');
        }

        $person->update($validated);

        return redirect()->route('personnel.index')->with('success', 'Personnel updated.');
    }

    public function destroy(Personnel $person)
    {
        if ($person->photo) {
            Storage::disk('public')->delete($person->photo);
        }

        $person->delete();

        return redirect()->route('personnel.index')->with('success', 'Personnel deleted.');
    }
}