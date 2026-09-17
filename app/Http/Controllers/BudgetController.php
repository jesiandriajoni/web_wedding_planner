<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Vendor;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function show(Project $project)
    {
        // Enforce member check
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        // Percentage-based default allocations
        $allocations = [
            'vendor' => (float) ($project->total_budget * 0.5),
            'katering' => (float) ($project->total_budget * 0.3),
            'seserahan' => (float) ($project->total_budget * 0.1),
            'lainnya' => (float) ($project->total_budget * 0.1),
        ];

        // Load all vendors with their payments
        $vendors = $project->vendors()->with('payments')->get();

        return Inertia::render('Projects/Budget', [
            'project' => $project,
            'allocations' => $allocations,
            'vendors' => $vendors
        ]);
    }

    public function storePayment(Request $request, Project $project, Vendor $vendor)
    {
        // Enforce member check
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        // Create payment (booted events will update vendor automatically)
        Payment::create([
            'project_id' => $project->id,
            'vendor_id' => $vendor->id,
            'amount' => $request->amount,
            'notes' => $request->notes,
        ]);

        return back();
    }
}
