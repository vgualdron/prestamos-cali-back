<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\QuestionServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Question;
    use App\Validator\{ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;
    
    class QuestionServiceImplement implements QuestionServiceInterface {

        use Commons;

        private $question;
        private $validator;
        private $profileValidator;

        function __construct(ProfileValidator $profileValidator){
            $this->question = new Question;
            $this->profileValidator = $profileValidator;
        }    

        function list(string $status, string $type) {
            try {
                $explodeStatus = explode(',', $status);
                $explodeType = explode(',', $type);
                $sql = $this->question->from('questions as q')
                    ->select(
                        'q.*',
                    )
                    // ->leftJoin('users as u', 'e.user_id', 'u.id')
                    ->when($status !== 'all', function ($q) use ($explodeStatus) {
                        return $q->whereIn('q.status', $explodeStatus);
                    })
                    ->when($type !== 'all', function ($q) use ($explodeType) {
                        return $q->whereIn('q.type', $explodeType);
                    })
                    ->orderBy('q.created_at', 'ASC')
                    ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
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
                            'text' => 'Se ha presentado un error al cargar los registros',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function create(array $question){
            try {
                DB::transaction(function () use ($question) {
                    $sql = $this->question::create([
                        'model_id' => $question['model_id'],
                        'model_name' => $question['model_name'],
                        'type' => $question['type'],
                        'status' => $question['status'],
                        'observation' => $question['observation'],
                        'area_id' => $question['area_id'],
                        'registered_by' => $question['registered_by'],
                    ]);
                });
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Solicitud de permiso registrado con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar nuevo',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function update(array $question, int $id){
            try {
                /* $validation = $this->validate($this->validator, $novel, $id, 'actualizar', 'nuevo', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                } */
                $sql = $this->novel::find($id);
                if(!empty($sql)) {
                    DB::transaction(function () use ($sql, $novel) {
                        $sql->document_number = $novel['documentNumber'];
                        $sql->name = $novel['name'];
                        $sql->phone = $novel['phone'];
                        $sql->address = $novel['address'];
                        $sql->sector = $novel['sector'];
                        $sql->status = $novel['status'];
                        $sql->district = $novel['district'];
                        $sql->occupation = $novel['occupation'];
                        $sql->observation = $novel['observation'];
                        $sql->user_send = $novel['userSend'] ? $novel['userSend'] : null;
                        $sql->save();
                    });
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Actualizado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al actualizar',
                                'detail' => 'El registro no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        
        function delete(int $id){   
            try {
                $sql = $this->expense::find($id);
                if(!empty($sql)) {
                    $sql->delete();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Registro eliminado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                    
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar el registro',
                                'detail' => 'El registro no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                if ($e->getCode() !== "23000") {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar el registro',
                                'detail' => $e->getMessage()
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se permite eliminar',
                                'detail' => $e->getMessage()
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        function get(int $id){
            try {
                $sql = $this->question->from('questions as q')
                    ->select(
                        'q.*',
                    )
                    ->leftJoin('yards as y', 'n.sector', 'y.id')
                    ->leftJoin('zones as z', 'y.zone', 'z.id')
                    ->leftJoin('users as u', 'n.user_send', 'u.id')
                    ->leftJoin('diaries as d', 'd.new_id', 'n.id')
                    ->leftJoin('users as us', 'us.id', 'd.user_id')
                    ->leftJoin('districts as dh', 'n.address_house_district', 'dh.id')
                    ->leftJoin('yards as yh', 'dh.sector', 'yh.id')
                    ->leftJoin('zones as zh', 'yh.zone', 'zh.id')
                    ->leftJoin('districts as dw', 'n.address_work_district', 'dw.id')
                    ->leftJoin('yards as yw', 'dw.sector', 'yw.id')
                    ->leftJoin('zones as zw', 'yw.zone', 'zw.id')
                    ->leftJoin('districts as drf', 'n.family_reference_district', 'drf.id')
                    ->leftJoin('yards as yrf', 'drf.sector', 'yrf.id')
                    ->leftJoin('zones as zrf', 'yrf.zone', 'zrf.id')
                    ->leftJoin('districts as drf2', 'n.family2_reference_district', 'drf2.id')
                    ->leftJoin('yards as yrf2', 'drf2.sector', 'yrf2.id')
                    ->leftJoin('zones as zrf2', 'yrf2.zone', 'zrf2.id')
                    ->leftJoin('districts as dg', 'n.guarantor_district', 'dg.id')
                    ->leftJoin('yards as yg', 'dg.sector', 'yg.id')
                    ->leftJoin('zones as zg', 'yg.zone', 'zg.id')
                    ->where('n.id', $id)
                    ->first();
                if(!empty($sql)) {
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'El registro no existe',
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
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function getStatus(array $question){
            try {
                $sql = $this->question->from('questions as q')
                    ->select(
                        'q.*',
                    )
                    // ->leftJoin('yards as y', 'n.sector', 'y.id')
                    ->where('q.model_id', $question['model_id'])
                    ->where('q.model_name', $question['model_name'])
                    ->where('q.type', $question['type'])
                    ->where('q.area_id', $question['area_id'])
                    ->first();
                if(!empty($sql)) {
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'data' => null
                    ], Response::HTTP_OK);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

    }
?>