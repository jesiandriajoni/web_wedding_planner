<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('projects.index');
})->middleware(['auth', 'verified'])->name('dashboard');

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PublicInvitationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\RundownController;
use App\Http\Controllers\SeserahanController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\AdminUserController;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::patch('/admin/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle_status');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember'])->name('projects.add_member');
    Route::get('/projects/{project}/dashboard', [DashboardController::class, 'show'])->name('projects.dashboard');
    Route::get('/projects/{project}/budget', [BudgetController::class, 'show'])->name('projects.budget');
    Route::post('/projects/{project}/vendors/{vendor}/payments', [BudgetController::class, 'storePayment'])->name('projects.payments.store');

    Route::get('/projects/{project}/vendors', [VendorController::class, 'index'])->name('projects.vendors.index');
    Route::post('/projects/{project}/vendors', [VendorController::class, 'store'])->name('projects.vendors.store');
    Route::put('/projects/{project}/vendors/{vendor}', [VendorController::class, 'update'])->name('projects.vendors.update');
    Route::delete('/projects/{project}/vendors/{vendor}', [VendorController::class, 'destroy'])->name('projects.vendors.destroy');

    Route::get('/projects/{project}/guests', [GuestController::class, 'index'])->name('projects.guests.index');
    Route::get('/projects/{project}/guests/template', [GuestController::class, 'downloadTemplate'])->name('projects.guests.template');
    Route::post('/projects/{project}/guests/import', [GuestController::class, 'import'])->name('projects.guests.import');
    Route::post('/projects/{project}/guests', [GuestController::class, 'store'])->name('projects.guests.store');
    Route::put('/projects/{project}/guests/{guest}', [GuestController::class, 'update'])->name('projects.guests.update');
    Route::delete('/projects/{project}/guests/{guest}', [GuestController::class, 'destroy'])->name('projects.guests.destroy');

    Route::get('/projects/{project}/rundowns', [RundownController::class, 'index'])->name('projects.rundowns.index');
    Route::post('/projects/{project}/rundowns', [RundownController::class, 'store'])->name('projects.rundowns.store');
    Route::put('/projects/{project}/rundowns/{rundown}', [RundownController::class, 'update'])->name('projects.rundowns.update');
    Route::delete('/projects/{project}/rundowns/{rundown}', [RundownController::class, 'destroy'])->name('projects.rundowns.destroy');
    Route::get('/projects/{project}/rundown/pdf', [RundownController::class, 'exportPdf'])->name('projects.rundown.pdf');

    Route::get('/projects/{project}/seserahan', [SeserahanController::class, 'index'])->name('projects.seserahan.index');
    Route::post('/projects/{project}/seserahan', [SeserahanController::class, 'store'])->name('projects.seserahan.store');
    Route::put('/projects/{project}/seserahan/{seserahan}', [SeserahanController::class, 'update'])->name('projects.seserahan.update');
    Route::delete('/projects/{project}/seserahan/{seserahan}', [SeserahanController::class, 'destroy'])->name('projects.seserahan.destroy');

    Route::get('/projects/{project}/guide', [GuideController::class, 'show'])->name('projects.guide.show');

    Route::get('/projects/{project}/checklists', [ChecklistController::class, 'index'])->name('projects.checklists.index');
    Route::post('/projects/{project}/checklists', [ChecklistController::class, 'store'])->name('projects.checklists.store');
    Route::put('/projects/{project}/checklists/{checklist}', [ChecklistController::class, 'update'])->name('projects.checklists.update');
    Route::delete('/projects/{project}/checklists/{checklist}', [ChecklistController::class, 'destroy'])->name('projects.checklists.destroy');

    Route::get('/projects/{project}/invitation', [InvitationController::class, 'edit'])->name('projects.invitation.edit');
    Route::post('/projects/{project}/invitation', [InvitationController::class, 'update'])->name('projects.invitation.update');
});

Route::get('/undangan/{slug}', [PublicInvitationController::class, 'show'])->name('public.invitation');
Route::post('/undangan/{slug}/rsvp', [PublicInvitationController::class, 'rsvp'])->name('public.invitation.rsvp');

require __DIR__.'/auth.php';
