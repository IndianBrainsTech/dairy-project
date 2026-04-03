<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\VehicleCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class VehicleCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $categories = VehicleCategory::withCount('vehicles')
                        ->latest()
                        ->get();

        return view('transport.vehicle-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('transport.vehicle-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:vehicle_categories,name',
            'description' => 'nullable|string|max:255',
            'status'      => 'required|in:active,inactive',
        ]);

        try {
            VehicleCategory::create([
                'name'        => $request->name,
                'description' => $request->description,
                'status'      => $request->status,
                'created_by'  => Auth::id(),
                'updated_by'  => Auth::id(),
            ]);

            return redirect()
                ->route('transport.vehicle-categories.index')
                ->with('success', 'Vehicle category created successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(VehicleCategory $vehicleCategory): View
    {
        $vehicleCategory->load('vehicles');
        return view('transport.vehicle-categories.show', compact('vehicleCategory'));
    }

    public function edit(VehicleCategory $vehicleCategory): View
    {
        return view('transport.vehicle-categories.edit', compact('vehicleCategory'));
    }

    public function update(Request $request, VehicleCategory $vehicleCategory): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:vehicle_categories,name,' . $vehicleCategory->id,
            'description' => 'nullable|string|max:255',
            'status'      => 'required|in:active,inactive',
        ]);

        try {
            $vehicleCategory->update([
                'name'        => $request->name,
                'description' => $request->description,
                'status'      => $request->status,
                'updated_by'  => Auth::id(),
            ]);

            return redirect()
                ->route('transport.vehicle-categories.index')
                ->with('success', 'Vehicle category updated successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(VehicleCategory $vehicleCategory): RedirectResponse
    {
        if ($vehicleCategory->vehicles()->exists()) {
            return back()->with('error', 'Cannot delete — vehicles are assigned to this category.');
        }

        $vehicleCategory->delete();

        return redirect()
            ->route('transport.vehicle-categories.index')
            ->with('success', 'Vehicle category deleted successfully.');
    }
}
