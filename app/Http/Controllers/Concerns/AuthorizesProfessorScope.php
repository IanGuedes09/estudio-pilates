<?php

namespace App\Http\Controllers\Concerns;

use App\Models\AgendaItem;
use App\Models\Aluno;
use App\Models\Pagamento;
use App\Models\User;

trait AuthorizesProfessorScope
{
    protected function authUser(): ?User
    {
        return auth()->user();
    }

    protected function isProfessorUser(): bool
    {
        return ($this->authUser()?->perfil ?? null) === 'Professor';
    }

    protected function isAdministradorUser(): bool
    {
        return ($this->authUser()?->perfil ?? null) === 'Administrador';
    }

    protected function currentProfessorId(): ?int
    {
        if (! $this->isProfessorUser()) {
            return null;
        }

        return $this->authUser()?->professorVinculado()?->id;
    }

    protected function requireCurrentProfessorId(): int
    {
        $id = $this->currentProfessorId();
        if ($id === null) {
            abort(403, 'Professor não vinculado ao cadastro.');
        }

        return $id;
    }

    protected function assertAlunoAccessibleAsProfessor(Aluno $aluno): void
    {
        if (! $this->isProfessorUser()) {
            return;
        }

        if ((int) $aluno->professor_id !== (int) $this->requireCurrentProfessorId()) {
            abort(403, 'Você não tem permissão para acessar esta aluna.');
        }
    }

    protected function assertAgendaItemAccessibleAsProfessor(AgendaItem $item): void
    {
        if (! $this->isProfessorUser()) {
            return;
        }

        if (! $item->aluno_id) {
            abort(403, 'Você não tem permissão para alterar este agendamento.');
        }

        $item->loadMissing('aluno');
        if (! $item->aluno) {
            abort(403);
        }

        $this->assertAlunoAccessibleAsProfessor($item->aluno);
    }

    protected function assertPagamentoAccessibleAsProfessor(Pagamento $pagamento): void
    {
        if (! $this->isProfessorUser()) {
            return;
        }

        if (! $pagamento->aluno_id) {
            abort(403, 'Você não tem permissão para acessar este pagamento.');
        }

        $pagamento->loadMissing('aluno');
        if (! $pagamento->aluno) {
            abort(403);
        }

        $this->assertAlunoAccessibleAsProfessor($pagamento->aluno);
    }
}
