<?php

use App\Http\Controllers\Admin\AvisController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('avis', [AvisController::class, 'index'])->name('avis.index');
Route::post('avis/{avis}/moderer', [AvisController::class, 'moderer'])->name('avis.moderer');
