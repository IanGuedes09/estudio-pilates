<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Professor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlunoController extends Controller
{
    public function index()
    {
        $query = Aluno::query()
            ->with('professor')
            ->withCount(['agendaItems', 'pagamentos'])
            ->orderBy('nome');

        if ($this->isProfessorUser()) {
            $query->where('professor_id', $this->requireCurrentProfessorId());
        }

        return view('alunos.index', [
            'alunos' => $query->get(),
        ]);
    }

    public function create()
    {
        return view('alunos.create', [
            'professores' => $this->professoresDisponiveisParaAluno(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:alunos,email',
            'telefone' => 'required|string|max:50',
            'cpf' => 'nullable|string|max:20',
            'plano_aulas_semana' => 'required|in:2,3',
            'professor_id' => 'nullable|exists:professores,id',
        ]);

        $data['valor_mensalidade'] = $this->mensalidadePorPlano((int) $data['plano_aulas_semana']);

        if ($this->isProfessorUser()) {
            $data['professor_id'] = $this->requireCurrentProfessorId();
        }

        Aluno::create($data);

        return redirect()->route('alunos.index')->with('success', 'Aluno cadastrado com sucesso!');
    }

    public function edit(Aluno $aluno)
    {
        $this->authorizeAlunoAccess($aluno);

        return view('alunos.edit', [
            'aluno' => $aluno,
            'professores' => $this->professoresDisponiveisParaAluno(),
        ]);
    }

    public function update(Request $request, Aluno $aluno)
    {
        $this->authorizeAlunoAccess($aluno);

        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:alunos,email,'.$aluno->id,
            'telefone' => 'required|string|max:50',
            'cpf' => 'nullable|string|max:20',
            'plano_aulas_semana' => 'required|in:2,3',
            'professor_id' => 'nullable|exists:professores,id',
        ]);

        $data['valor_mensalidade'] = $this->mensalidadePorPlano((int) $data['plano_aulas_semana']);

        if ($this->isProfessorUser()) {
            $data['professor_id'] = $this->requireCurrentProfessorId();
        }

        $aluno->update($data);

        return redirect()->route('alunos.index')->with('success', 'Aluno atualizado com sucesso!');
    }

    public function destroy(Aluno $aluno)
    {
        $this->authorizeAlunoAccess($aluno);

        $today = Carbon::today()->toDateString();
        $competenciaAtual = Carbon::today()->format('Y-m');
        $nomeHistorico = $aluno->nome;

        DB::transaction(function () use ($aluno, $today, $competenciaAtual, $nomeHistorico) {
            // Remove apenas agenda futura (ou sem data definida).
            $aluno->agendaItems()
                ->where(function ($q) use ($today) {
                    $q->whereNull('data')
                        ->orWhere('data', '>=', $today);
                })
                ->delete();

            // Remove todos os pagamentos futuros/atuais do aluno excluído.
            $aluno->pagamentos()
                ->where('competencia', '>=', $competenciaAtual)
                ->delete();

            // Histórico anterior permanece, apenas desvincula do cadastro para permitir exclusão.
            $aluno->pagamentos()->update([
                'aluno_id' => null,
                'aluna_nome' => $nomeHistorico,
            ]);

            $aluno->agendaItems()->update([
                'aluno_id' => null,
                'aluna_nome' => $nomeHistorico,
            ]);

            $aluno->delete();
        });

        return redirect()->route('alunos.index')->with('success', 'Aluno excluído. Agenda e pagamentos futuros foram removidos, histórico anterior foi preservado.');
    }

    private function professoresDisponiveisParaAluno()
    {
        if ($this->isProfessorUser()) {
            return Professor::query()
                ->whereKey($this->requireCurrentProfessorId())
                ->where('ativo', true)
                ->orderBy('nome')
                ->get();
        }

        // Usuários com acesso ao sistema que também podem atuar como professor.
        $usuariosElegiveis = User::query()
            ->whereIn('perfil', ['Administrador', 'Professor'])
            ->get(['id', 'name', 'email']);

        foreach ($usuariosElegiveis as $usuario) {
            $professor = Professor::query()->firstOrNew(['user_id' => $usuario->id]);

            if (!$professor->exists) {
                $professor->nome = $usuario->name;
                $professor->email = $usuario->email;
                $professor->ativo = true;
                $professor->comissao_percentual = $professor->comissao_percentual ?? 0;
                $professor->save();
                continue;
            }

            // Mantém cadastro sincronizado sem sobrescrever campos já definidos manualmente.
            $dirty = false;
            if (!$professor->nome) {
                $professor->nome = $usuario->name;
                $dirty = true;
            }
            if (!$professor->email) {
                $professor->email = $usuario->email;
                $dirty = true;
            }
            if ($dirty) {
                $professor->save();
            }
        }

        return Professor::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();
    }

    private function isProfessorUser(): bool
    {
        return (auth()->user()?->perfil ?? null) === 'Professor';
    }

    private function currentProfessorId(): ?int
    {
        return auth()->user()?->professor?->id;
    }

    private function authorizeAlunoAccess(Aluno $aluno): void
    {
        if (!$this->isProfessorUser()) {
            return;
        }

        if ((int) $aluno->professor_id !== (int) $this->requireCurrentProfessorId()) {
            abort(403, 'Você só pode acessar alunos vinculados ao seu cadastro.');
        }
    }

    private function requireCurrentProfessorId(): int
    {
        $id = $this->currentProfessorId();
        if (!$id) {
            abort(403, 'Seu usuário professor não está vinculado ao cadastro de professor.');
        }

        return (int) $id;
    }

    private function mensalidadePorPlano(int $planoAulasSemana): float
    {
        return $planoAulasSemana === 3 ? 300.0 : 200.0;
    }
}
