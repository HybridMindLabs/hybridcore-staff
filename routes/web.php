<?php

use Hybridcore\Staff\Http\Controllers\Web\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
