<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\WorkplanServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Workplan;
    use App\Validator\WorkplanValidator;
    use App\Traits\Commons;

    class WorkplanServiceImplement implements WorkplanServiceInterface {

        use Commons;

        private $workplan;
        private $validator;

        function __construct(WorkplanValidator $validator){
            $this->workplan = new Workplan;
            $this->validator = $validator;
        }

        function list(){
            try {
                $sql = $this->district->from('districts as d')
                    ->select(
                        'd.id',
                        'd.name',
                        'd.sector',
                        'd.status',
                        'd.group',
                        'd.order',
                        'y.name as sectorName',
                        'z.name as cityName',
                    )
                    ->join('yards as y', 'd.sector', 'y.id')
                    ->join('zones as z', 'y.zone', 'z.id')
                    ->orderBy('z.code', 'asc')
                    ->orderBy('d.group', 'asc')
                    ->orderBy('d.order', 'asc')
                    ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay registros para mostrar',
                                'detail' => 'Aún no se ha registrado ninguno'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
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
                $validation = $this->validate($this->validator, $district, null, 'registrar', 'workplan', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $status = $this->district::create($workplan);
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

        function get(int $id){
            try {
                $sql = $this->district->from('districts as d')
                    ->select('d.id', 'd.name', 'd.sector', 'd.status', 'd.group', 'd.order', 'y.name as sectorName')
                    ->join('yards as y', 'd.sector', 'y.id')
                    ->where('id', $id)
                    ->first();
                if(!empty($sql)) {
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No existe',
                                'detail' => 'por favor recargue la página'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar',
                            'detail' => $e->getMessage(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>
