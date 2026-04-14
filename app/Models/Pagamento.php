<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pagamento extends Model
{
    protected $table = 'pagamentos';

    protected $fillable = [
        'aluno_id',
        'aluna_nome',
        'professora_nome',
        'competencia',
        'valor_bruto',
        'desconto',
        'valor_liquido',
        'metodo',
        'status',
        'data_pagamento',
        'percentual_comissao',
        'valor_comissao',
        'valor_estudio',
        'observacoes',
    ];

    protected $casts = [
        'data_pagamento' => 'date:Y-m-d',
    ];

    protected static function booted(): void
    {
        static::saving(function (Pagamento $pagamento) {
            if ($pagamento->aluno_id) {
                $nome = Aluno::query()->whereKey($pagamento->aluno_id)->value('nome');
                if ($nome) {
                    $pagamento->aluna_nome = $nome;
                }
            }
        });
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function comprovantes(): HasMany
    {
        return $this->hasMany(PagamentoComprovante::class);
    }
}
