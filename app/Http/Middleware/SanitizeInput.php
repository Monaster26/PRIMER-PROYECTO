<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de Sanitización de Entradas (Anti-XSS).
 *
 * Recorre recursivamente todos los inputs del request y aplica:
 * - htmlspecialchars para escapar caracteres HTML peligrosos
 * - strip_tags para eliminar etiquetas HTML no deseadas
 * - trim para limpiar espacios
 *
 * Excluye campos de contraseña y tokens para no corromperlos.
 */
class SanitizeInput
{
    /**
     * Campos que nunca deben sanitizarse (contraseñas, tokens, JSON encodeds).
     */
    private array $except = [
        'password',
        'password_confirmation',
        'pin',
        '_token',
        'session_token',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        $sanitized = $this->sanitize($input);
        $request->replace($sanitized);

        return $next($request);
    }

    private function sanitize(array $input): array
    {
        foreach ($input as $key => $value) {
            if (in_array($key, $this->except, true)) {
                continue;
            }

            if (is_array($value)) {
                $input[$key] = $this->sanitize($value);
            } elseif (is_string($value)) {
                // 1. Eliminar etiquetas HTML
                $value = strip_tags($value);
                // 2. Convertir caracteres especiales a entidades HTML
                $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                // 3. Eliminar espacios sobrantes
                $input[$key] = trim($value);
            }
        }

        return $input;
    }
}
