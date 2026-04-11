<?php

namespace App\Http\Controllers;

use App\Models\Avis;
use App\Models\Departement;
use App\Models\Plombier;

class HomeController extends Controller
{
    public function index()
    {
        $departements = Departement::orderBy('departement')->get();
        $derniersAvis = Avis::approved()->with(['plombier', 'user'])->latest()->limit(6)->get();
        $plombiersUrgence = Plombier::valide()->where('urgence_24h', true)->with('horairesRelation')->inRandomOrder()->limit(6)->get();

        return view('home', compact('departements', 'derniersAvis', 'plombiersUrgence'));
    }
}
