<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\TaskServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Task;
    use App\Traits\Commons;
    use Illuminate\Support\Facades\DB;

    class TaskServiceImplement implements TaskServiceInterface {

        use Commons;

        private $task;

        function __construct(){
            $this->task = new Task;
        }

        function list(string $status) {
            try {
                $explodeStatus = explode(',', $status);
                $sql = $this->task->from('tasks as t')
                    ->select('t.*')
                    ->orderBy('priority', 'DESC')
                    ->get();

                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
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

        function create(array $task){
            try {
                $status = $this->task::create($task);
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

        function update(array $task, int $id){
            try {
                $sql = $this->task::find($id)->update($task);

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
                $sql = $this->task::find($id);
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
