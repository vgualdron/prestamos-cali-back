<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\ReddirectionServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Reddirection;
    use App\Validator\{ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;
    
    class ReddirectionServiceImplement implements ReddirectionServiceInterface {

        use Commons;

        private $reddirection;
        private $profileValidator;

        function __construct(ProfileValidator $profileValidator){
            $this->reddirection = new Reddirection;
            $this->profileValidator = $profileValidator;
        }    

        function create(array $reddirection){
            try {
                DB::transaction(function () use ($reddirection) {
                    $sql = $this->reddirection::create([
                        'collector_id' => $reddirection['collector_id'],
                        'registered_by' => $reddirection['idUserSesion'],
                        'registered_date' => date('Y-m-d H:i:s'),
                        'lending_id' => $reddirection['lending_id'],
                        'address' => $reddirection['address'],
                        'district_id' => $reddirection['district_id'],
                        'type_ref' => $reddirection['type_ref'],
                        'description_ref' => $reddirection['description_ref'],
                        'value' => $reddirection['value'],
                        'status' => $reddirection['status'],
                    ]);
                });
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
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function getCurrentByUser(int $user){
            try {
                $item = $this->reddirection->from('reddirections as rd')
                                        ->select(
                                            'rd.*',
                                            'l.firstDate as lending_first_date',
                                            'l.endDate as lending_end_date',
                                            'li.id as listing_id',
                                            'li.name as listing_name',
                                            'd.name as district_name',
                                            'd.order as district_order',
                                            'n.observation as new_observation',
                                            'n.name as new_name',
                                            'y.name as sector_name',
                                            'f.latitude as address_latitude',
                                            'f.longitude as address_longitude',
                                            'u.latitude as user_latitude',
                                            'u.longitude as user_longitude',
                                        )
                                        ->leftJoin('lendings as l', 'l.id', 'rd.lending_id')
                                        ->leftJoin('news as n', 'n.id', 'l.new_id')
                                        ->leftJoin('listings as li', 'li.id', 'l.listing_id')
                                        ->leftJoin('districts as d', 'd.id', 'rd.district_id')
                                        ->leftJoin('yards as y', 'y.id', 'd.sector')
                                        ->leftJoin('users as u', 'u.id', 'rd.collector_id')
                                        ->leftJoin('files as f', function($join) {
                                            $join->where('f.model_name', '=', 'news')
                                                 ->on('f.model_id', '=', 'n.id')
                                                 ->where('f.name', '=', 'PDF_CV');
                                        })
                                        ->where('rd.collector_id', $user)
                                        ->where('rd.status', 'activo')
                                        ->first();
                return response()->json([
                    'data' => $item
                ], Response::HTTP_OK);
                
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

        function update(array $reddirection, int $id){
            try {
                $sql = $this->reddirection::find($id);
                if(!empty($sql)) {
                    $sql->collector_id = $reddirection['collector_id'];
                    $sql->approved_by = $reddirection['approved_by'];
                    $sql->approved_date = $reddirection['approved_date'];
                    $sql->start_date = $reddirection['start_date'];
                    $sql->end_date = $reddirection['end_date'];
                    $sql->file_id = $reddirection['file_id'];
                    $sql->file2_id = $reddirection['file2_id'];
                    $sql->status = $reddirection['status'];
                    $sql->attended = $reddirection['attended'];
                    $sql->solution = $reddirection['solution'];
                    $sql->observation = $reddirection['observation'];
                    $sql->save();
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
                                'detail' => 'No existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
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

        function getByLending(int $lending){
            try {
                $item = $this->reddirection->from('reddirections as rd')
                                        ->select(
                                            'rd.*',
                                            'l.firstDate as lending_first_date',
                                            'l.endDate as lending_end_date',
                                            'li.id as listing_id',
                                            'li.name as listing_name',
                                            'd.name as district_name',
                                            'd.order as district_order',
                                            'n.observation as new_observation',
                                            'n.name as new_name',
                                            'y.name as sector_name',
                                            'f1.url as file_url',
                                            'f1.latitude as file_latitude',
                                            'f1.longitude as file_longitude',
                                            'f2.url as file2_url',
                                            'f2.latitude as file2_latitude',
                                            'f2.longitude as file2_longitude',
                                            'u.name as collector_name',
                                        )
                                        ->leftJoin('lendings as l', 'l.id', 'rd.lending_id')
                                        ->leftJoin('news as n', 'n.id', 'l.new_id')
                                        ->leftJoin('listings as li', 'li.id', 'l.listing_id')
                                        ->leftJoin('districts as d', 'd.id', 'rd.district_id')
                                        ->leftJoin('yards as y', 'y.id', 'd.sector')
                                        ->leftJoin('users as u', 'u.id', 'rd.collector_id')
                                        ->leftJoin('files as f1', 'f1.id', 'rd.file_id')
                                        ->leftJoin('files as f2', 'f2.id', 'rd.file2_id')
                                        ->where('rd.lending_id', $lending)
                                        ->where('rd.status', 'activo')
                                        ->orderBy('rd.address', 'ASC')
                                        ->orderBy('rd.registered_date', 'DESC')
                                        ->get();
                return response()->json([
                    'data' => $item
                ], Response::HTTP_OK);
                
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