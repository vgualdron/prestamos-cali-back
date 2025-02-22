<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\WorkplanServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Workplan;
    use App\Validator\WorkplanValidator;
    use App\Traits\Commons;
    use Illuminate\Support\Facades\DB;

    class WorkplanServiceImplement implements WorkplanServiceInterface {

        use Commons;

        private $workplan;
        private $validator;

        function __construct(WorkplanValidator $validator){
            $this->workplan = new Workplan;
            $this->validator = $validator;
        }

        function list(string $date){
            try {
                $sql = "SELECT
                        s.id AS '#',
                        IFNULL(MAX(CASE WHEN w.listing_id = 1 THEN w.status ELSE NULL END), 'pendiente') AS 'Ruta 10',
                        IFNULL(MAX(CASE WHEN w.listing_id = 2 THEN w.status ELSE NULL END), 'pendiente') AS 'Ruta 23',
                        IFNULL(MAX(CASE WHEN w.listing_id = 3 THEN w.status ELSE NULL END), 'pendiente') AS 'Ruta 179',
                        IFNULL(MAX(CASE WHEN w.listing_id = 4 THEN w.status ELSE NULL END), 'pendiente') AS 'Ruta 234',
                        IFNULL(MAX(CASE WHEN w.listing_id = 5 THEN w.status ELSE NULL END), 'pendiente') AS 'Ruta 568',
                        IFNULL(MAX(CASE WHEN w.listing_id = 23 THEN w.status ELSE NULL END), 'pendiente') AS 'Ruta 9',
                        IFNULL(MAX(CASE WHEN w.listing_id = 6 THEN w.status ELSE NULL END), 'pendiente') AS 'Ruta 128',
                        IFNULL(MAX(CASE WHEN w.listing_id = 7 THEN w.status ELSE NULL END), 'pendiente') AS 'Ruta 345',
                        IFNULL(MAX(CASE WHEN w.listing_id = 8 THEN w.status ELSE NULL END), 'pendiente') AS 'Ruta 671',
                        IFNULL(MAX(CASE WHEN w.listing_id = 9 THEN w.status ELSE NULL END), 'pendiente') AS 'Ruta 910',
                        IFNULL(MAX(CASE WHEN w.listing_id = 10 THEN w.status ELSE NULL END), 'pendiente') AS 'Ruta 17',
                        IFNULL(MAX(CASE WHEN w.listing_id = 11 THEN w.status ELSE NULL END), 'pendiente') AS 'Ruta 111'
                    FROM steps s
                    LEFT JOIN workplans w
                        ON s.id = w.step_id
                        AND DATE(w.registered_date) BETWEEN '" . $date . " 00:00:00 ' AND '" . $date . " 23:59:59' GROUP BY s.id ORDER BY s.id ASC;";

                $results = DB::select($sql);

                if (count($results) > 0){
                    return response()->json([
                        'data' => $results
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'data' => []
                    ], Response::HTTP_OK);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar',
                            'detail' => $e->getMessage(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


        function create(array $workplan){
            try {
                $validation = $this->validate($this->validator, $workplan, null, 'registrar', 'workplan', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $status = $this->workplan::create($workplan);
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Registrado con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar',
                            'detail' => $e->getMessage(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function update(array $workplan, int $id){
            try {
                $validation = $this->validate($this->validator, $district, $id, 'actualizar', 'workplan', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->workplan::find($id)->update($workplan);

                return response()->json([
                    'message' => [
                        [
                            'text' => 'Actualizado con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar',
                            'detail' => $e->getMessage(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function delete(int $id){
            try {
                $sql = $this->workplan::find($id);
                if(!empty($sql)) {
                    $sql->delete();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Eliminado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar',
                                'detail' => 'No existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                if ($e->getCode() !== "23000") {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar',
                                'detail' => $e->getMessage(),
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se permite eliminar el registro',
                                'detail' => $e->getMessage(),
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

    }
?>
