<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $projects = $user->role === 'admin'
            ? Project::all()
            : $user->projects;

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'isAdmin' => $user->role === 'admin',
        ]);
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'wedding_date' => 'required|date',
            'total_budget' => 'required|numeric|min:0',
            'pengantin_name' => 'required|string|max:255',
            'pengantin_email' => 'required|string|email|max:255|unique:users,email',
            'pengantin_password' => 'required|string|min:8',
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (Project::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $project = Project::create([
            'name' => $request->name,
            'slug' => $slug,
            'wedding_date' => $request->wedding_date,
            'total_budget' => $request->total_budget,
        ]);

        // Create the pengantin account and attach it to the project
        $pengantin = User::create([
            'name' => $request->pengantin_name,
            'email' => $request->pengantin_email,
            'password' => Hash::make($request->pengantin_password),
            'role' => 'pengantin',
        ]);

        $project->users()->attach($pengantin->id, ['role' => 'pengantin']);

        // Admin is not a project member; send back to the project list
        return redirect()->route('projects.index');
    }

    public function show(Project $project)
    {
        // Enforce member check
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $project->load(['users', 'checklists', 'guests', 'vendors', 'rundowns', 'seserahanItems']);

        return Inertia::render('Projects/Show', [
            'project' => $project
        ]);
    }

    public function addMember(Request $request, Project $project)
    {
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:wo,pengantin,keluarga',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($project->users()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['email' => 'User is already a member of this project.']);
        }

        $project->users()->attach($user->id, ['role' => $request->role]);

        return back();
    }

    public function update(Request $request, Project $project)
    {
        if (!$project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'wedding_date' => 'required|date',
            'total_budget' => 'required|numeric|min:0',
        ]);

        $project->update([
            'name' => $request->name,
            'wedding_date' => $request->wedding_date,
            'total_budget' => $request->total_budget,
        ]);

        return back();
    }
}
