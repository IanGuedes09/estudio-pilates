<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'perfil', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function professor(): HasOne
    {
        return $this->hasOne(Professor::class);
    }

    /**
     * Garante linha em `professores` com `user_id` para perfil Professor.
     * O seeder só cria `users`; sem isso, gestão de alunos e APIs retornam 403.
     */
    public function professorVinculado(): ?Professor
    {
        $existing = $this->professor()->first();
        if ($existing) {
            return $existing;
        }

        if (($this->perfil ?? null) !== 'Professor') {
            return null;
        }

        $orphan = Professor::query()
            ->whereNull('user_id')
            ->where('email', $this->email)
            ->first();

        if ($orphan) {
            $orphan->user_id = $this->id;
            if (! $orphan->nome) {
                $orphan->nome = $this->name;
            }
            $orphan->ativo = true;
            $orphan->save();
            $this->setRelation('professor', $orphan);

            return $orphan;
        }

        $created = $this->professor()->create([
            'nome' => $this->name,
            'email' => $this->email,
            'ativo' => true,
            'comissao_percentual' => 0,
        ]);
        $this->setRelation('professor', $created);

        return $created;
    }
}
