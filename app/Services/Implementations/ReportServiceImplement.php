<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\ReportServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Report;
    use App\Traits\Commons;
    use Illuminate\Support\Facades\DB;

    class ReportServiceImplement implements ReportServiceInterface {

        use Commons;

        private $report;

        function __construct(){
            $this->report = new Report;
        }

        function list(){
            try {
                $sql = $this->report->select('id', 'name', 'order', 'permission', 'background', 'color')
                            ->orderBy('order', 'ASC')->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay reportes',
                                'detail' => 'Aun no ha registrado ningún reporte',
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los reportes',
                            'detail' => $e->getMessage(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function execute(int $id){
            try {
                $report = $this->report->select(
                    'id',
                    'name',
                    'sql',
                    'order')
                    ->where('id', $id)
                    ->first();

                DB::statement("SET time_zone = 'America/Bogota'");
                $rows = DB::select($report->sql);

                if (count($rows) > 0) {
                    return response()->json([
                        'data' => $rows
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay registros',
                                'detail' => 'Aun no ha registros para ver este reporte',
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los reportes',
                            'detail' => $e->getMessage(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

    }
?>
