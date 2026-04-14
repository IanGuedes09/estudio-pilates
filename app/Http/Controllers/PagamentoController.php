<?php

namespace App\Http\Controllers;

class PagamentoController extends Controller
{
    // Renderiza a tela de pagamentos (dados via API no banco).
    public function index()
    {
        return view('pagamentos.index');
    }
}
