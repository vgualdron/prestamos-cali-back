<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    protected function unauthenticated($request, array $guards)
    {
        // Obtener la fecha enviada en el header
        $clientTimestamp = $request->header('X-Client-Timestamp');

        // Verificar si el header existe
        if (!$clientTimestamp) {
            abort(response()->json([
                'message' => [
                    [
                        'text' => 'Falta la fecha y hora del cliente',
                        'detail' => 'Por favor, asegúrese de enviar el header X-Client-Timestamp en la petición',
                    ]
                ],
            ], 400));
        }

        try {
            // Convertir la fecha del cliente a un objeto Carbon
            $clientDateTime = Carbon::parse($clientTimestamp);
            $serverDateTime = Carbon::now();

            // Definir el tiempo máximo de diferencia permitida (ejemplo: 5 minutos)
            $maxDifferenceMinutes = 5;

            // Validar si la fecha es demasiado antigua o en el futuro
            if ($clientDateTime->diffInMinutes($serverDateTime) > $maxDifferenceMinutes) {
                abort(response()->json([
                    'message' => [
                        [
                            'text' => 'Fecha y hora desactualizadas',
                            'detail' => 'La diferencia entre la hora del servidor y la del cliente no debe superar los 5 minutos.',
                        ]
                    ],
                ], 400));
            }
        } catch (\Exception $e) {
            abort(response()->json([
                'message' => [
                    [
                        'text' => 'Formato de fecha inválido',
                        'detail' => 'Por favor, envíe la fecha en formato ISO 8601 (Ejemplo: 2025-02-16T12:34:56Z)',
                    ]
                ],
            ], 400));
        }

        // Si el usuario no está autenticado, devolver el error de autenticación
        abort(response()->json([
            'message' => [
                [
                    'text' => 'No hay una sesión activa',
                    'detail' => 'Por favor, cierre su sesión local e iníciela nuevamente',
                ]
            ],
        ], 401));
    }
}
