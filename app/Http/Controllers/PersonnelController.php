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

   public function show(Request $request, Personnel $person)
{
    $query = $person->propertyItems()->with('category');

    // filter by category if selected
    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    $grouped = $query->orderBy('description')->orderBy('property_no')->get()
        ->groupBy(fn ($item) => $item->description . '|' . $item->category_id . '|' . $item->type)
        ->map(function ($items) {
            $first = $items->first();
            // gather every property number across the grouped items (expanding ranges)
            $allNumbers = $items->flatMap(fn ($i) => $i->propertyNoList())->values()->all();
            return (object) [
                'description' => $first->description,
                'category' => $first->category->name ?? '—',
                'type' => $first->type,
                'unit' => $first->unit,
                'qty' => $items->sum('on_hand_per_count'),
                'numbers' => $allNumbers,
            ];
        })
        ->values();

    // categories this person actually has property in (for the filter dropdown)
    $personCategories = \App\Models\PropertyCategory::whereIn('id',
        $person->propertyItems()->distinct()->pluck('category_id')
    )->orderBy('name')->get();

    return view('personnel.show', compact('person', 'grouped', 'personCategories'));
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