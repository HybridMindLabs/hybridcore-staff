<?php

use Hybridcore\Staff\Http\Controllers\Admin\StaffController;
use Illuminate\Support\Facades\Route;

// Loaded with web + auth + admin middleware and the /admin prefix by the core.
Route::get('/staff', [StaffController::class, 'index'])
    ->middleware('perm:staff.view')
    ->name('admin.staff.index');

Route::middleware('perm:staff.manage')->group(function (): void {
    Route::post('/staff', [StaffController::class, 'store'])->name('admin.staff.store');
    Route::put('/staff/{member}', [StaffController::class, 'update'])->name('admin.staff.update');
    Route::delete('/staff/{member}', [StaffController::class, 'destroy'])->name('admin.staff.destroy');
});
