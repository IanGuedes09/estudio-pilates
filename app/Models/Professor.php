<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Professor extends Model
{
    use HasFactory;

    protected $table = 'professores';

    protected $fillable = [
        'user_id',
        'nome',
        'cpf',
        'email',
        'telefone',
        'comissao_percentual',
        'ativo',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'comissao_percentual' => 'decimal:2',
        ];
    }

    public function getCpfFormatadoAttribute(): ?string
    {
        $d = preg_replace('/\D/', '', (string) $this->cpf);
        if (strlen($d) !== 11) {
            return $this->cpf ?: null;
        }

        return substr($d, 0, 3).'.'.substr($d, 3, 3).'.'.substr($d, 6, 3).'-'.substr($d, 9, 2);
    }
}
