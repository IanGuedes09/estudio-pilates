<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aluno extends Model
{
    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'valor_mensalidade',
        'plano_aulas_semana',
        'professor_id',
        'endereco',
        'cep',
        'cpf',
        'rg',
        'data_nascimento',
        'sexo',
    ];

    protected function casts(): array
    {
        return [
            'valor_mensalidade' => 'decimal:2',
            'plano_aulas_semana' => 'integer',
        ];
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Professor::class);
    }

    public function agendaItems(): HasMany
    {
        return $this->hasMany(AgendaItem::class);
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }
}
