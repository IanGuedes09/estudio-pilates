<?php

namespace App\Http\Controllers;

class AgendaController extends Controller
{
    // Tela front-first da agenda, consumindo /api/v1/agenda.
    public function index()
    {
        return view('agenda.index');
    }
}
