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

        if ($request->has('total_budget')) {
            $cleanBudget = is_string($request->total_budget)
                ? str_replace(['.', ','], '', $request->total_budget)
                : $request->total_budget;
            $request->merge(['total_budget' => $cleanBudget]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'wedding_date' => 'required|date',
            'total_budget' => 'required|numeric|min:0',
            'pengantin_name' => 'nullable|required_with:pengantin_email,pengantin_password|string|max:255',
            'pengantin_email' => 'nullable|required_with:pengantin_name,pengantin_password|string|email|max:255|unique:users,email',
            'pengantin_password' => 'nullable|required_with:pengantin_name,pengantin_email|string|min:8',
        ], [
            'name.required' => 'Nama judul project pernikahan wajib diisi.',
            'wedding_date.required' => 'Tanggal pernikahan wajib diisi.',
            'total_budget.required' => 'Total anggaran wajib diisi.',
            'total_budget.numeric' => 'Total anggaran harus berupa angka.',
            'pengantin_name.required_with' => 'Nama akun pengantin wajib diisi jika membuat akun pengantin.',
            'pengantin_email.required_with' => 'Email pengantin wajib diisi jika membuat akun pengantin.',
            'pengantin_email.email' => 'Format email pengantin tidak valid.',
            'pengantin_email.unique' => 'Email pengantin ini sudah terdaftar di sistem. Silakan gunakan email lain.',
            'pengantin_password.required_with' => 'Kata sandi pengantin wajib diisi jika membuat akun pengantin.',
            'pengantin_password.min' => 'Kata sandi pengantin minimal 8 karakter.',
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (Project::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$count;
            $count++;
        }

        $project = Project::create([
            'name' => $request->name,
            'slug' => $slug,
            'wedding_date' => $request->wedding_date,
            'total_budget' => $request->total_budget,
        ]);

        // Create the pengantin account if provided and attach it to the project
        if ($request->filled('pengantin_email')) {
            $pengantin = User::create([
                'name' => $request->pengantin_name,
                'email' => $request->pengantin_email,
                'password' => Hash::make($request->pengantin_password),
                'role' => 'pengantin',
                'is_active' => true,
            ]);

            $project->users()->attach($pengantin->id, ['role' => 'pengantin']);
        }

        return redirect()->route('projects.index')->with('success', 'Project pernikahan berhasil dibuat.');
    }

    public function show(Project $project)
    {
        // Enforce member check
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $project->load(['users', 'checklists', 'guests', 'vendors', 'rundowns', 'seserahanItems']);

        return Inertia::render('Projects/Show', [
            'project' => $project,
        ]);
    }

    public function addMember(Request $request, Project $project)
    {
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
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
        if (! $project->users()->where('user_id', auth()->id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->has('total_budget')) {
            $cleanBudget = is_string($request->total_budget)
                ? str_replace(['.', ','], '', $request->total_budget)
                : $request->total_budget;
            $request->merge(['total_budget' => $cleanBudget]);
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
