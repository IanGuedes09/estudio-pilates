<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use Illuminate\Http\Request;

class AlunoController extends Controller
{
    public function index()
    {
        return view('alunos.index', [
            'alunos' => Aluno::query()->orderBy('nome')->get(),
        ]);
    }

    public function create()
    {
        return view('alunos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email',
            'telefone' => 'required|string|max:50',
            'cpf' => 'nullable|string|max:20',
        ]);

        Aluno::create($data);

        return redirect()->route('alunos.index')->with('success', 'Aluno cadastrado com sucesso!');
    }
}
