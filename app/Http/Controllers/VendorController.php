<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VendorController extends Controller
{
    public function index(Project $project)
    {
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $vendors = $project->vendors()->get();

        // Group vendors by category for price comparison list
        $comparison = $vendors->groupBy('category')->map(function ($grouped) {
            return $grouped->map(fn($v) => [
                'name' => $v->name,
                'price' => (float) $v->package_price,
            ])->sortBy('price')->values();
        });

        return Inertia::render('Projects/Vendors', [
            'project' => $project,
            'vendors' => $vendors,
            'comparison' => $comparison
        ]);
    }

    public function store(Request $request, Project $project)
    {
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'package_price' => 'required|numeric|min:0',
            'mou' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // max 10MB
        ]);

        $mouPath = null;
        if ($request->hasFile('mou')) {
            // Save file in secure subfolder with unique name
            $mouPath = $request->file('mou')->store('contracts', 'public');
        }

        $project->vendors()->create([
            'name' => $request->name,
            'category' => $request->category,
            'contact' => $request->contact,
            'package_price' => $request->package_price,
            'paid_amount' => 0.00,
            'status' => 'pending',
            'mou_path' => $mouPath
        ]);

        return back();
    }

    public function update(Request $request, Project $project, Vendor $vendor)
    {
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'package_price' => 'required|numeric|min:0',
        ]);

        $vendor->update([
            'name' => $request->name,
            'category' => $request->category,
            'contact' => $request->contact,
            'package_price' => $request->package_price,
        ]);

        // Trigger auto update for paid status
        $vendor->updatePaidAmountAndStatus();

        return back();
    }

    public function destroy(Project $project, Vendor $vendor)
    {
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $vendor->delete();

        return back();
    }
}
