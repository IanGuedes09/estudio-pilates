<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use Illuminate\Http\JsonResponse;

class AlunoApiController extends Controller
{
    public function index(): JsonResponse
    {
        $alunos = Aluno::query()
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return response()->json($alunos);
    }
}
