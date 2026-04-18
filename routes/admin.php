<?php

use App\Http\Controllers\Admin\AvisController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DemandeController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PlombierController;
use App\Http\Controllers\Admin\ReclamationController;
use App\Http\Controllers\Admin\StatsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('stats', [StatsController::class, 'index'])->name('stats');

// Plombiers
Route::get('plombiers', [PlombierController::class, 'index'])->name('plombiers.index');
Route::get('plombiers/{plombier}/edit', [PlombierController::class, 'edit'])->name('plombiers.edit');
Route::put('plombiers/{plombier}', [PlombierController::class, 'update'])->name('plombiers.update');
Route::post('plombiers/{plombier}/toggle-valide', [PlombierController::class, 'toggleValide'])->name('plombiers.toggle-valide');
Route::delete('plombiers/{plombier}', [PlombierController::class, 'destroy'])->name('plombiers.destroy');

// Avis
Route::get('avis', [AvisController::class, 'index'])->name('avis.index');
Route::post('avis/{avis}/moderer', [AvisController::class, 'moderer'])->name('avis.moderer');

// Demandes
Route::get('demandes', [DemandeController::class, 'index'])->name('demandes.index');
Route::get('demandes/{demande}', [DemandeController::class, 'show'])->name('demandes.show');
Route::post('demandes/{demande}/statut', [DemandeController::class, 'updateStatut'])->name('demandes.update-statut');
Route::delete('demandes/{demande}', [DemandeController::class, 'destroy'])->name('demandes.destroy');

// Messages
Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

// Réclamations de fiches
Route::get('reclamations', [ReclamationController::class, 'index'])->name('reclamations.index');
Route::post('reclamations/{claim}', [ReclamationController::class, 'update'])->name('reclamations.update');
