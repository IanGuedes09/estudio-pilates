<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlunoApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Aluno::query()
            ->with('professor:id,nome')
            ->orderBy('nome');

        if (($request->user()?->perfil ?? null) === 'Professor') {
            $query->whereHas('professor', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        }

        $alunos = $query
            ->get(['id', 'nome', 'professor_id', 'plano_aulas_semana'])
            ->map(fn (Aluno $aluno) => [
                'id' => $aluno->id,
                'nome' => $aluno->nome,
                'professor_nome' => $aluno->professor?->nome,
                'plano_aulas_semana' => (int) ($aluno->plano_aulas_semana ?: 2),
            ]);

        return response()->json($alunos);
    }
}
