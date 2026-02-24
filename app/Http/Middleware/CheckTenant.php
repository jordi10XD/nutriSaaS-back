<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $empresaId = $request->header('X-Empresa-ID');
        if (!$empresaId) {
            return response()->json(['message' => 'Falta el encabezado X-Empresa-ID requerido para esta ruta.'], 401);
        }

        $empresa = \App\Models\Company::on('master')->find($empresaId);
        if (!$empresa) {
            error_log('CheckTenant: Empresa not found for ID ' . $empresaId);
            return response()->json(['message' => 'Empresa no encontrada.'], 404);
        }

        error_log('CheckTenant: Swapping connection to ' . $empresa->nombre_bd);

        // Dynamically set the database for the tenant connection
        \config(['database.connections.tenant.database' => $empresa->nombre_bd]);
        \config(['database.default' => 'tenant']);

        // Switch the default connection to use the new tenant DB for this request lifecycle
        \Illuminate\Support\Facades\DB::setDefaultConnection('tenant');

        // Authenticate the user against the new tenant database cleanly!
        if (!auth('sanctum')->check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Set the default guard so $request->user() works
        \Illuminate\Support\Facades\Auth::shouldUse('sanctum');

        return $next($request);
    }
}
