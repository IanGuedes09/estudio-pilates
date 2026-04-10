<?php

namespace App\Http\Controllers;

class PagamentoController extends Controller
{
    // Renderiza a tela de pagamentos (dados vindos do endpoint mock).
    public function index()
    {
        return view('pagamentos.index');
    }
}
