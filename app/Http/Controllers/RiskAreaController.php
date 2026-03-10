<?php

namespace App\Http\Controllers;

use App\Models\RiskArea;
use Illuminate\Http\Request;

class RiskAreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $riskAreas = RiskArea::all();
        return view('admin.risk-areas.index', compact('riskAreas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.risk-areas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'heading' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:High,Medium,Low',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        RiskArea::create($validated);

        return redirect()->route('risk-areas.index')
            ->with('success', 'Risk area created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RiskArea $riskArea)
    {
        return view('admin.risk-areas.edit', compact('riskArea'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RiskArea $riskArea)
    {
        $validated = $request->validate([
            'heading' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:High,Medium,Low',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $riskArea->update($validated);

        return redirect()->route('risk-areas.index')
            ->with('success', 'Risk area updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RiskArea $riskArea)
    {
        $riskArea->delete();

        return redirect()->route('risk-areas.index')
            ->with('success', 'Risk area deleted successfully.');
    }
}
