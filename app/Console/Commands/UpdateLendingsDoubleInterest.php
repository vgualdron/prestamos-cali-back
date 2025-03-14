<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateLendingsDoubleInterest extends Command
{
    /**
     * Nombre y firma del comando.
     */
    protected $signature = 'update:double-interest';

    /**
     * Descripción del comando.
     */
    protected $description = 'Actualizar los registros con doble interés según las condiciones especificadas';

    /**
     * Lógica del comando.
     */
    public function handle()
    {
        // Ejecutar la consulta para obtener los registros que cumplen las condiciones
        $lendingsToUpdate = DB::select('
            SELECT
            lendings.id AS lending_id,
            lendings.amount AS amount,
            lendings.firstDate AS date,
            DATEDIFF(CURRENT_DATE, lendings.firstDate) AS days_since_first_date,
            DATE_ADD(lendings.firstDate, INTERVAL 15 DAY) AS new_date,
            IFNULL(SUM(payments.amount), 0) AS total_paid,
            (lendings.amount * (lendings.percentage / 100)) AS expected_interest
        FROM
            lendings
        LEFT JOIN
            payments ON lendings.id = payments.lending_id
        WHERE
            lendings.status = "open"
            AND lendings.has_double_interest = false
            AND DATEDIFF(CURRENT_DATE, lendings.firstDate) >= 15
        GROUP BY
            lendings.id
        HAVING
            expected_interest > total_paid
        ORDER BY
            lendings.id;
        ');

        // Actualizar los registros seleccionados
        foreach ($lendingsToUpdate as $lending) {
            DB::table('lendings')
                ->where('id', $lending->lending_id)
                ->update([
                    'has_double_interest' => true,
                    'doubleDate' => $lending->new_date, // Actualizar con la fecha actual
                ]);
        }

        // Registrar el resultado en el log
        $this->info(count($lendingsToUpdate) . ' --> registros actualizados con doble interés.');
        \Log::info('Registros actualizados con doble interés.', [
            'count' => count($lendingsToUpdate),
        ]);
    }
}
