<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\MockVitaliData;
use Illuminate\Http\JsonResponse;

class MockVitaliApiController extends Controller
{
    // Endpoint de resumo para cards do dashboard.
    public function dashboardResumo(): JsonResponse
    {
        return response()->json(MockVitaliData::dashboardResumo());
    }

    // Lista completa da agenda mockada.
    public function agenda(): JsonResponse
    {
        return response()->json(MockVitaliData::agenda());
    }

    // Detalhe de item da agenda por ID.
    public function agendaShow(int $id): JsonResponse
    {
        $item = collect(MockVitaliData::agenda())->firstWhere('id', $id);
        abort_if(!$item, 404, 'Agenda item not found');

        return response()->json($item);
    }

    // Lista de pagamentos com valores de comissao.
    public function pagamentos(): JsonResponse
    {
        return response()->json(MockVitaliData::pagamentos());
    }

    // Detalhe de pagamento por ID.
    public function pagamentosShow(int $id): JsonResponse
    {
        $item = collect(MockVitaliData::pagamentos())->firstWhere('id', $id);
        abort_if(!$item, 404, 'Pagamento not found');

        return response()->json($item);
    }

    // Lista de comprovantes anexados.
    public function comprovantes(): JsonResponse
    {
        return response()->json(MockVitaliData::comprovantes());
    }

    // Detalhe de comprovante por ID.
    public function comprovantesShow(int $id): JsonResponse
    {
        $item = collect(MockVitaliData::comprovantes())->firstWhere('id', $id);
        abort_if(!$item, 404, 'Comprovante not found');

        return response()->json($item);
    }
}
