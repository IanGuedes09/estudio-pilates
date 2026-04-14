<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PerfilMiddleware
{
    public function handle(Request $request, Closure $next, string ...$perfis): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Acesso não autorizado.');
        }

        if (empty($perfis) || in_array($user->perfil, $perfis, true)) {
            return $next($request);
        }

        abort(403, 'Você não tem permissão para acessar este módulo.');
    }
}
