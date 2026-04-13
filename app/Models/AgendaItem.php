<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaItem extends Model
{
    protected $fillable = [
        'aluno_id',
        'aluna_nome',
        'professora_nome',
        'data',
        'hora_inicio',
        'hora_fim',
        'tipo_aula',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data' => 'date:Y-m-d',
    ];

    protected static function booted(): void
    {
        static::saving(function (AgendaItem $item) {
            if ($item->aluno_id) {
                $nome = Aluno::query()->whereKey($item->aluno_id)->value('nome');
                if ($nome) {
                    $item->aluna_nome = $nome;
                }
            }
        });
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }
}
