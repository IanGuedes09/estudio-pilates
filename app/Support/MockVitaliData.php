<?php

namespace App\Support;

class MockVitaliData
{
    // Payload usado no dashboard principal.
    public static function dashboardResumo(): array
    {
        return [
            'alunas_ativas' => 42,
            'aulas_hoje' => 14,
            'receita_mes' => 8450.00,
            'comissao_mes' => 2800.00,
            'lucro_estudio_mes' => 5650.00,
            'proximas_aulas' => [
                [
                    'hora_inicio' => '14:00',
                    'aluna_nome' => 'Lorrane Magalhães',
                    'tipo_aula' => 'Pilates Solo',
                ],
                [
                    'hora_inicio' => '15:30',
                    'aluna_nome' => 'Karine Designer',
                    'tipo_aula' => 'Pilates Aparelho',
                ],
                [
                    'hora_inicio' => '16:30',
                    'aluna_nome' => 'Rafaela Ferreira',
                    'tipo_aula' => 'Alongamento',
                ],
            ],
        ];
    }

    // Itens de agenda para consumo do front enquanto o banco nao estiver pronto.
    public static function agenda(): array
    {
        return [
            [
                'id' => 1,
                'data' => '2026-04-10',
                'hora_inicio' => '14:00',
                'hora_fim' => '14:50',
                'aluna' => ['id' => 12, 'nome' => 'Karine Designer'],
                'professora' => ['id' => 2, 'nome' => 'Prof. Ana', 'tipo' => 'TARDE'],
                'tipo_aula' => 'Pilates Aparelho',
                'status' => 'AGENDADA',
                'observacoes' => 'Primeira aula experimental',
            ],
            [
                'id' => 2,
                'data' => '2026-04-10',
                'hora_inicio' => '15:30',
                'hora_fim' => '16:20',
                'aluna' => ['id' => 18, 'nome' => 'Lorrane Magalhães'],
                'professora' => ['id' => 1, 'nome' => 'Karine', 'tipo' => 'MANHA'],
                'tipo_aula' => 'Pilates Solo',
                'status' => 'CONFIRMADA',
                'observacoes' => null,
            ],
        ];
    }

    // Pagamentos simulados com dados de comissao para validar regras de negocio no front.
    public static function pagamentos(): array
    {
        return [
            [
                'id' => 55,
                'aluna_id' => 12,
                'aluna_nome' => 'Karine Designer',
                'competencia' => '2026-04',
                'valor_bruto' => 350.00,
                'desconto' => 0,
                'valor_liquido' => 350.00,
                'metodo' => 'PIX',
                'status' => 'PAGO',
                'data_pagamento' => '2026-04-10',
                'professora_id' => 2,
                'professora_nome' => 'Prof. Ana',
                'percentual_comissao' => 45,
                'valor_comissao' => 157.50,
                'valor_estudio' => 192.50,
                'observacoes' => 'Mensalidade abril',
            ],
            [
                'id' => 56,
                'aluna_id' => 18,
                'aluna_nome' => 'Lorrane Magalhães',
                'competencia' => '2026-04',
                'valor_bruto' => 420.00,
                'desconto' => 20.00,
                'valor_liquido' => 400.00,
                'metodo' => 'CARTAO',
                'status' => 'PENDENTE',
                'data_pagamento' => null,
                'professora_id' => 1,
                'professora_nome' => 'Karine',
                'percentual_comissao' => 0,
                'valor_comissao' => 0,
                'valor_estudio' => 400.00,
                'observacoes' => 'Aguardando compensacao',
            ],
        ];
    }

    // Comprovantes simulados para lista/download.
    public static function comprovantes(): array
    {
        return [
            [
                'id' => 101,
                'pagamento_id' => 55,
                'nome_arquivo' => 'comprovante_pix_abril.pdf',
                'url' => '/storage/comprovantes/comprovante_pix_abril.pdf',
                'mime_type' => 'application/pdf',
                'tamanho_bytes' => 248392,
                'enviado_por' => 1,
                'created_at' => '2026-04-10T14:22:00Z',
            ],
            [
                'id' => 102,
                'pagamento_id' => 56,
                'nome_arquivo' => 'recibo_cartao_abril.png',
                'url' => '/storage/comprovantes/recibo_cartao_abril.png',
                'mime_type' => 'image/png',
                'tamanho_bytes' => 128992,
                'enviado_por' => 1,
                'created_at' => '2026-04-11T09:05:00Z',
            ],
        ];
    }
}
