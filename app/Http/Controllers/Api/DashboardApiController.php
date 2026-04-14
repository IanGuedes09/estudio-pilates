<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DashboardResumo;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends Controller
{
    public function resumo(): JsonResponse
    {
        return response()->json(DashboardResumo::make(auth()->user()));
    }
}
