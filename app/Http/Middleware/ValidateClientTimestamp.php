<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class ValidateClientTimestamp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
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
            // Convertir la fecha del cliente a un objeto Carbon con formato explícito y zona horaria UTC
            $clientDateTime = Carbon::createFromFormat('Y-m-d\TH:i:s', $clientTimestamp, 'UTC');
            var_dump($clientDateTime);

            // Asegurarse de que la hora está en UTC
            $clientDateTime->setTimezone('UTC');

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
                        'detail' => $e->getMessage(),
                    ]
                ],
            ], 400));
        }

        return $next($request);
    }
}
