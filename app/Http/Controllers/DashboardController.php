<?php

namespace App\Http\Controllers;

use App\Support\DashboardResumo;

class DashboardController extends Controller
{
    public function index()
    {
        $resumo = DashboardResumo::make(auth()->user());

        return view('dashboard', [
            'resumo' => $resumo,
            'proximasAulas' => $resumo['proximas_aulas'],
        ]);
    }
}
