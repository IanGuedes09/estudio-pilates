<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AlunoController extends Controller
{
    public function index()
{
    // Listar todos os alunos (veremos isso depois)
    return view('alunos.index');
}

public function create()
{
    // Mostrar o formulário de cadastro
    return view('alunos.create');
}

public function store (Request $request)
{
    // 1. Validação básica para não salvar lixo no banco
    $request->validate([
        'nome' => 'required|string|max:255',
        'email' => 'required|email',
        'telefone' => 'required',
    ]);

    // 2. Criar o aluno com os dados do formulário
    Aluno::create($request->all());

    // 3. Redirecionar de volta com uma mensagem de sucesso
    return redirect()->route('alunos.index')->with('success', 'Aluno cadastrado com sucesso!');
}
}
