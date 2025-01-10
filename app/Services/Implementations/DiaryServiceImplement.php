<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\DiaryServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Diary;
    use App\Models\Novel;
    use App\Validator\{DiaryValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;

    class DiaryServiceImplement implements DiaryServiceInterface {

        use Commons;

        private $diary;
        private $validator;

        function __construct(DiaryValidator $validator){
            $this->diary = new Diary;
            $this->novel = new Novel;
            $this->validator = $validator;
        }

        function list(string $date, int $user, string $moment) {
            try {
                $dates = $this->getDatesOfWeek($date, $moment);

                $sql = $this->diary
                    ->from('diaries as d')
                    ->select(
                        'd.id',
                        'd.user_id',
                        'u.name as userName',
                        'd.date',
                        'd.new_id',
                        'n.name as new_name',
                        'n.address as new_address',
                        'n.address_work',
                        'n.address_house',
                        'n.site_visit',
                        'n.district as new_district',
                        'b.name as new_districtName',
                        'b.order as new_districtOrder',
                        'dh.name as new_districtHouseName',
                        'dh.order as new_districtHouseOrder',
                        'dw.name as new_districtWorkName',
                        'dw.order as new_districtWorkOrder',
                        'n.occupation as new_occupation',
                        'n.phone as new_phone',
                        'n.status as new_status',
                        'd.status',
                        'd.observation',
                        's.name as sectorName',
                        'n.visit_start_date',
                        'n.visit_end_date',
                    )
                    ->leftJoin('users as u', 'd.user_id', 'u.id')
                    ->leftJoin('news as n', 'd.new_id', 'n.id')
                    ->leftJoin('yards as s', 'n.sector', 's.id')
                    ->leftJoin('districts as b', 'n.district', 'b.id')
                    ->leftJoin('districts as dh', 'n.address_house_district', 'dh.id')
                    ->leftJoin('districts as dw', 'n.address_work_district', 'dw.id')
                    ->where('user_id', $user)
                    ->whereBetween('date', ["$dates[0] 00:00:00", "$dates[5] 23:59:59"]) // TO DO, ajustar para que muestre solo las visitas adegndadas o en visitando, o las del dia actual.
                    ->orderBy('date', 'ASC')
                    ->orderByRaw("
                        CASE
                            WHEN n.site_visit = 'casa' THEN CAST(SUBSTRING_INDEX(dh.order, 'A', 1) AS UNSIGNED)
                            WHEN n.site_visit = 'trabajo' THEN CAST(SUBSTRING_INDEX(dw.order, 'A', 1) AS UNSIGNED)
                            ELSE 0
                        END ASC
                    ")
                    ->orderByRaw("
                        CASE
                            WHEN n.site_visit = 'casa' THEN SUBSTRING(dh.order, -1)
                            WHEN n.site_visit = 'trabajo' THEN SUBSTRING(dw.order, -1)
                            ELSE ''
                        END ASC
                    ")
                    ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'data' => [],
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

        function listDayByDay(string $date, int $user, string $moment) {
            try {
                $dates = $this->getDatesOfWeek($date, $moment);
                $days = $this->getHoursOfDay();
                $data = [];
                foreach ($days as $i => $valueDay) {
                    foreach ($dates as $j => $valueDate) {
                        $sql = $this->diary->from('diaries as d')
                        ->select(
                            'd.id',
                            'd.user_id',
                            'u.name as userName',
                            'd.date',
                            'd.new_id',
                            'n.name as new_name',
                            'n.address as new_address',
                            'n.address_work',
                            'n.address_house',
                            'n.site_visit',
                            'n.district as new_district',
                            'b.name as new_districtName',
                            'b.order as new_districtOrder',
                            'dh.name as new_districtHouseName',
                            'dh.order as new_districtHouseOrder',
                            'dw.name as new_districtWorkName',
                            'dw.order as new_districtWorkOrder',
                            'n.occupation as new_occupation',
                            'n.phone as new_phone',
                            'n.status as new_status',
                            'd.status',
                            'd.observation',
                            's.name as sectorName',
                            'n.visit_start_date',
                            'n.visit_end_date',
                        )
                        ->leftJoin('users as u', 'd.user_id', 'u.id')
                        ->leftJoin('news as n', 'd.new_id', 'n.id')
                        ->leftJoin('yards as s', 'n.sector', 's.id')
                        ->leftJoin('districts as b', 'n.district', 'b.id')
                        ->leftJoin('districts as dh', 'n.address_house_district', 'dh.id')
                        ->leftJoin('districts as dw', 'n.address_work_district', 'dw.id')
                        ->where('user_id', $user)
                        ->where('date', "$valueDate $valueDay")
                        ->orderBy('date', 'ASC')
                        ->first();

                        if ($sql) {
                            $data[$i]["date"] = $valueDate;
                            if ($j == 0) {
                                $data[$i]["items"][$j] = $sql;
                            }
                            $data[$i]["items"][$j + 1] = $sql;
                        } else {
                            $data[] = [];
                        }
                    }
                }

                return response()->json([
                    'data' => $data
                ], Response::HTTP_OK);

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

        function listVisitsReview(string $date) {
            try {
                $sql = $this->diary->from('diaries as d')
                ->select(
                    'd.id',
                    'd.user_id',
                    'u.name as userName',
                    'd.date',
                    'd.new_id',
                    'n.name as new_name',
                    'n.address as new_address',
                    'n.address_work',
                    'n.address_house',
                    'n.site_visit',
                    'n.district as new_district',
                    'b.name as new_districtName',
                    'n.occupation as new_occupation',
                    'n.phone as new_phone',
                    'n.status as new_status',
                    'd.status',
                    'd.observation',
                    's.name as sectorName',
                )
                ->join('users as u', 'd.user_id', 'u.id')
                ->join('news as n', 'd.new_id', 'n.id')
                ->leftJoin('yards as s', 'n.sector', 's.id')
                ->leftJoin('districts as b', 'n.district', 'b.id')
                ->where('date', "<=", "$date 23:59:59")
                ->where('d.status', "<>", "finalizada")
                // ->whereDate('date', "<=", DB::raw("DATE_ADD($date, INTERVAL 2 DAY)"))
                ->orderBy('date', 'ASC')
                ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'data' => [],
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

        function getStatusCases(int $idNew) {
            try {
                $data = [];
                $sql = $this->diary->from('news as n')
                ->select(
                    'n.*'
                )->where('id', "=", $idNew)
                ->get()
                ->first();

                $files = $this->diary->from('files as f')
                ->select(
                    'f.*'
                )->where('model_id', "=", $idNew)
                ->where('model_name', "=", "news")
                ->get();

                $data['CASA CLIENTE']['NOMBRE'] = $sql->name ? true : false;
                $data['CASA CLIENTE']['DOCUMENTO'] = $sql->document_number ? true : false;
                $data['CASA CLIENTE']['DIRECCION CASA'] = $sql->address_house ? true : false;
                $data['CASA CLIENTE']['DIRECCION TRABAJO'] = $sql->address_work ? true : false;
                $data['CASA CLIENTE']['OCUPACION'] = $sql->occupation ? true : false;
                $data['CASA CLIENTE']['TIPO CASA'] = $sql->type_house ? true : false;
                $data['CASA CLIENTE']['TIPO TRABAJAO'] = $sql->type_work ? true : false;
                $data['CASA CLIENTE']['CANTIDAD'] = $sql->quantity ? true : false;
                $data['CASA CLIENTE']['PERIODO'] = $sql->period ? true : false;

                $nameFile = "FOTO_CASA_CLIENTE";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['CASA CLIENTE'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "VIDEO_TOCANDO_CASA_CLIENTE";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['CASA CLIENTE'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_CLIENTE";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['CASA CLIENTE'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_CEDULA_CLIENTE_FRONTAL";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['CASA CLIENTE'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_CEDULA_CLIENTE_POSTERIOR";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['CASA CLIENTE'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_LETRA_CLIENTE";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['CASA CLIENTE'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_FIRMANDO_LETRA_CLIENTE";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['CASA CLIENTE'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_CERTIFICADO_TRABAJO_CLIENTE";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['CASA CLIENTE'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_RECIBO_CASA_CLIENTE";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['CASA CLIENTE'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_CERTIFICADO_TRABAJO_CLIENTE";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['TRABAJO'][$nameFile] = $file && $file->status === "aprobado" ? true : false;
                $data['TRABAJO']["DIRECCION TRABAJO"] = $sql->address_work ? true : false;

                $nameFile = "FOTO_RECIBO_CASA_CLIENTE";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['CASA PROPIA'][$nameFile] = $file && $file->status === "aprobado" ? true : false;
                $data['CASA PROPIA']["CASA PROPIA"] = $sql->type_house === 'propia' ? true : false;

                $data['REFERENCIA 1']['NOMBRE'] = $sql->family_reference_name ? true : false;
                $data['REFERENCIA 1']['DIRECCION'] = $sql->family_reference_address ? true : false;
                $data['REFERENCIA 1']['TELEFONO'] = $sql->family_reference_phone ? true : false;
                $data['REFERENCIA 1']['PARENTESCO'] = $sql->family_reference_relationship ? true : false;

                $nameFile = "VIDEO_REFERENCIA_FAMILIAR_1";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['REFERENCIA 1'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_CASA_REFERENCIA_FAMILIAR_1";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['REFERENCIA 1'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $data['REFERENCIA 2']['NOMBRE'] = $sql->family2_reference_name ? true : false;
                $data['REFERENCIA 2']['DIRECCION'] = $sql->family2_reference_address ? true : false;
                $data['REFERENCIA 2']['TELEFONO'] = $sql->family2_reference_phone ? true : false;
                $data['REFERENCIA 2']['PARENTESCO'] = $sql->family2_reference_relationship ? true : false;

                $nameFile = "VIDEO_REFERENCIA_FAMILIAR_2";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['REFERENCIA 2'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_CASA_REFERENCIA_FAMILIAR_2";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['REFERENCIA 2'][$nameFile] = $file && $file->status === "aprobado" ? true : false;


                $data['FIADOR']['DOCUMENTO'] = $sql->guarantor_document_number ? true : false;
                $data['FIADOR']['OCUPACION'] = $sql->guarantor_occupation ? true : false;
                $data['FIADOR']['NOMBRE'] = $sql->guarantor_name ? true : false;
                $data['FIADOR']['DIRECCION'] = $sql->guarantor_address ? true : false;
                $data['FIADOR']['TELEFONO'] = $sql->guarantor_phone ? true : false;
                $data['FIADOR']['PARENTESCO'] = $sql->guarantor_relationship ? true : false;

                $nameFile = "FOTO_CASA_FIADOR";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['FIADOR'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_FIADOR";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['FIADOR'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_CEDULA_FIADOR_FRONTAL";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['FIADOR'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_CEDULA_FIADOR_POSTERIOR";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['FIADOR'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_LETRA_FIADOR";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['FIADOR'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_FIRMANDO_LETRA_FIADOR";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['FIADOR'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_CERTIFICADO_TRABAJO_FIADOR";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['FIADOR'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                $nameFile = "FOTO_RECIBO_CASA_FIADOR";
                $file = $files->first(function($file) use ($nameFile) {
                    return $file["name"] == $nameFile;
                });
                $data['FIADOR'][$nameFile] = $file && $file->status === "aprobado" ? true : false;

                return response()->json([
                    'data' => $data
                ], Response::HTTP_OK);
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

        function approveVisit(array $diary) {
            try {
                /* $validation = $this->validate($this->validator, $novel, $id, 'actualizar', 'nuevo', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                } */
                $sqlDiary = $this->diary::find($diary['diary_id']);
                $sqlNovel = $this->novel::find($diary['id']);
                if(!empty($sqlDiary) && !empty($sqlNovel)) {
                     DB::transaction(function () use ($sqlDiary, $sqlNovel, $diary) {
                        $sqlDiary->status = 'finalizada';
                        $sqlDiary->save();

                        $sqlNovel->observation = $diary['novel_observation'];
                        $sqlNovel->status = $diary['novel_status'];
                        $sqlNovel->approved_date = date('Y-m-d H:i:s');
                        $sqlNovel->visit_end_date = date('Y-m-d H:i:s');
                        $sqlNovel->approved_by = $diary['idUserSesion'];
                        $sqlNovel->lent_by = $diary['userVisit'];
                        $sqlNovel->save();
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

        function create(array $diary) {
            try {
                $sql = $this->diary::create([
                    'date' => $diary['date'] . ' ' . date('H:i:s'),
                    'user_id' => $diary['user_id'],
                    'new_id' => $diary['new_id'],
                    'status' => $diary['status'],
                    'observation' => '',
                ]);

                return response()->json([
                    'message' => [
                        [
                            'text' => 'Agendado con éxito',
                            'detail' => $sql
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function update(array $diary, int $id) {
            try {
                $validation = $this->validate($this->validator, $diary, $id, 'actualizar', 'agenda', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->diary::find($id);
                if(!empty($sql)) {
                    DB::transaction(function () use ($sql, $diary) {
                        $sql->status = $diary['status'];
                        $sql->new_id = $diary['new_id'];
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

        function updateStatus(array $novel, int $id) {
            try {
                $validation = $this->validate($this->validator, $novel, $id, 'actualizar', 'nuevo', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->novel::find($id);
                if(!empty($sql)) {
                     DB::transaction(function () use ($sql, $novel) {
                        $sql->document_number = $novel['documentNumber'];
                        $sql->name = $novel['name'];
                        $sql->phone = $novel['phone'];
                        $sql->address = $novel['address'];
                        $sql->sector = $novel['sector'];
                        $sql->status = $novel['status'];
                        $sql->attempts = $novel['attempts'];
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
                $sql = $this->novel::find($id);
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
                $sql = $this->diary::select(
                    'id',
                    'document_number as documentNumber',
                    'name',
                    'yard',
                    'phone',
                    'active',
                    'editable',
                    'change_yard as changeYard'
                )
                    ->where('id', $id)
                    ->first();
                if(!empty($sql)) {
                    $roles = $sql->roles->pluck('id');
                    unset($sql->roles);
                    $sql->roles = $roles;
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

        function getDatesOfWeek($date, $moment = 'current') {
            $fecha = strtotime($date);
            $weekNumber = date("W", $fecha);
            if ($moment == 'next') {
                $weekNumber += 1;
            }
            $year = date("Y", $fecha);

            $dates = [];

            // Crear una fecha para el primer día de la semana específica
            $firstDayOfWeek = strtotime($year . "W" . str_pad($weekNumber, 2, '0', STR_PAD_LEFT));

            // Obtener las fechas de la semana
            for ($i = 0; $i < 6; $i++) {
                $dates[] = date('Y-m-d', strtotime("+$i day", $firstDayOfWeek));
            }

            return $dates;
        }

        function getHoursOfDay() {
            $dates[0] = "08:00:00";
            $dates[1] = "08:30:00";
            $dates[2] = "09:00:00";
            $dates[3] = "09:30:00";
            $dates[4] = "10:00:00";
            $dates[5] = "10:30:00";
            $dates[6] = "11:00:00";
            $dates[7] = "11:30:00";
            $dates[8] = "14:00:00";
            $dates[9] = "14:30:00";
            $dates[10] = "15:00:00";
            $dates[11] = "15:30:00";
            $dates[12] = "16:00:00";
            $dates[13] = "16:30:00";
            $dates[14] = "17:00:00";
            $dates[15] = "17:30:00";
            return $dates;
        }

    }
?>
