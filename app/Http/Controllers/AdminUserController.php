<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $users = User::where('role', 'pengantin')->get();

        return Inertia::render('Admin/Users', [
            'users' => $users
        ]);
    }
}
