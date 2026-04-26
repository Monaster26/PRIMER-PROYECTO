<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de Autenticación POS.
 *
 * Valida que el request contenga un employee_id y session_token válidos
 * antes de permitir acceso a endpoints protegidos del POS.
 *
 * El token se envía como Header: X-POS-Token
 * El employee ID se envía como Header: X-POS-Employee
 */
class PosAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $employeeId   = $request->header('X-POS-Employee') ?? $request->input('employee_id');
        $sessionToken = $request->header('X-POS-Token')    ?? $request->input('session_token');

        if (!$employeeId || !$sessionToken) {
            return response()->json([
                'success' => false,
                'message' => 'Autenticación POS requerida.',
            ], 401);
        }

        $employee = Employee::active()->find($employeeId);

        if (!$employee || !$employee->validateSessionToken($sessionToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión POS inválida o expirada. Inicia sesión nuevamente.',
            ], 401);
        }

        // Inyectar el empleado autenticado en el request
        $request->merge(['_pos_employee' => $employee]);
        $request->attributes->set('pos_employee', $employee);

        return $next($request);
    }
}
