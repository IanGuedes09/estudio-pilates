<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagamentoComprovante extends Model
{
    protected $table = 'pagamento_comprovantes';

    protected $fillable = [
        'pagamento_id',
        'competencia',
        'nome_arquivo',
        'caminho_arquivo',
        'url',
        'mime_type',
        'tamanho_bytes',
        'enviado_por',
    ];

    public function pagamento(): BelongsTo
    {
        return $this->belongsTo(Pagamento::class);
    }
}
