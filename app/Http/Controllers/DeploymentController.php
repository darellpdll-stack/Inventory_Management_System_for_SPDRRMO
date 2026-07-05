<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\SupplyItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeploymentController extends Controller
{
    public function index()
    {
        $deployments = Deployment::with('releasedBy', 'items')
            ->orderByDesc('deployed_at')
            ->paginate(10);

        return view('deployments.index', compact('deployments'));
    }

    public function create()
    {
        // only items that have stock to give out
        $items = SupplyItem::with('category')
            ->where('current_stock', '>', 0)
            ->orderBy('item_name')
            ->get();

        return view('deployments.create', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'required|string|max:255',
            'purpose' => 'nullable|string|max:255',
            'authorized_by' => 'required|string|max:255',
            'deployed_at' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.supply_item_id' => 'required|exists:supply_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $deployment = DB::transaction(function () use ($validated) {
            $deployment = Deployment::create([
                'destination' => $validated['destination'],
                'purpose' => $validated['purpose'] ?? null,
                'authorized_by' => $validated['authorized_by'],
                'released_by' => Auth::id(),
                'status' => 'deployed',
                'deployed_at' => $validated['deployed_at'],
            ]);

            foreach ($validated['items'] as $line) {
                $item = SupplyItem::lockForUpdate()->findOrFail($line['supply_item_id']);

                if ($item->current_stock < $line['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Not enough stock for {$item->item_name}. Available: {$item->current_stock}.",
                    ]);
                }

                $item->decrement('current_stock', $line['quantity']);

                $deployment->items()->create([
                    'supply_item_id' => $item->id,
                    'quantity' => $line['quantity'],
                ]);
            }

            return $deployment;
        });

        return redirect()->route('deployments.show', $deployment)
            ->with('success', 'Deployment recorded and stock updated.');
    }

    public function show(Deployment $deployment)
    {
        $deployment->load('items.supplyItem.category', 'releasedBy');
        return view('deployments.show', compact('deployment'));
    }
}