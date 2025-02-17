<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ValidateClientTimestamp
{
    public function handle(Request $request, Closure $next)
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

        // Convertir la fecha del cliente a un objeto Carbon
        $clientDateTime = Carbon::parse($clientTimestamp);
        $serverDateTime = Carbon::now();

        // Definir el tiempo máximo de diferencia permitida (ejemplo: 5 minutos)
        $maxDifferenceMinutes = 5;

        // Validar si la fecha es demasiado antigua o en el futuro
        if ($clientDateTime->diffInMinutes($serverDateTime) > $maxDifferenceMinutes) {
            //abort(400, 'Fecha y hora desactualizadas. La diferencia entre la hora del servidor y la del cliente no debe superar los 5 minutos.');
            abort(response()->json([
                'message' => [
                    [
                        'text' => 'Fecha y hora desactualizadas',
                        'detail' => 'La diferencia entre la hora del servidor y la del cliente no debe superar los 5 minutos.',
                    ]
                ],
            ], 400));
        }

        return $next($request);
    }
}
