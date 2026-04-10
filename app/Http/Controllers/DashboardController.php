<?php

namespace App\Http\Controllers;

use App\Support\MockVitaliData;

class DashboardController extends Controller
{
    // Entrega dados mockados para montar o dashboard rapidamente.
    public function index()
    {
        $resumo = MockVitaliData::dashboardResumo();

        return view('dashboard', [
            'resumo' => $resumo,
            'proximasAulas' => $resumo['proximas_aulas'],
        ]);
    }
}
