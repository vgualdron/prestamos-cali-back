<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\NovelServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Novel;
    use App\Models\Question;
    use App\Validator\{NovelValidator, ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;
    
    class NovelServiceImplement implements NovelServiceInterface {

        use Commons;

        private $novel;
        private $validator;
        private $profileValidator;

        function __construct(NovelValidator $validator, ProfileValidator $profileValidator){
            $this->novel = new Novel;
            $this->validator = $validator;
            $this->profileValidator = $profileValidator;
        }    

        function list(string $status) {
            try {
                $explodeStatus = explode(',', $status);
                $sql = $this->novel->from('news as n')
                    ->select(
                        'n.id',
                        'n.document_number as documentNumber',
                        'n.name as name',
                        'n.phone as phone',
                        'n.address as address',
                        'n.address_house',
                        'n.address_house_district',
                        'dh.name as districtHouseName',
                        'n.address_work',
                        'n.address_work_district',
                        'dw.name as districtWorkName',
                        'n.site_visit',
                        'n.district as district',
                        'd.name as districtName',
                        'd.group as districtGroup',
                        'd.order as districtOrder',
                        'n.occupation as occupation',
                        'n.attempts as attempts',
                        'n.observation as observation',
                        'n.status as status',
                        'n.created_at as date',
                        'n.visit_start_date',
                        DB::Raw('IF(y.zone IS NOT NULL, z.name, "Sin ciudad") as cityName'),
                        DB::Raw('IF(y.zone IS NOT NULL, z.id, null) as city'),
                        DB::Raw('IF(n.sector IS NOT NULL, y.name, "Sin sector") as sectorName'),
                        DB::Raw('IF(n.sector IS NOT NULL, y.id, null) as sector'),
                        DB::Raw('IF(n.user_send IS NOT NULL, u.name, "Ninguno") as userSendName'),
                        DB::Raw('IF(n.user_send IS NOT NULL, u.id, null) as userSend'),
                        'n.family_reference_district',
                        'n.family_reference_name',
                        'n.family_reference_address',
                        'n.family_reference_phone',
                        'n.family_reference_relationship',
                        'n.family2_reference_district',
                        'n.family2_reference_name',
                        'n.family2_reference_address',
                        'n.family2_reference_phone',
                        'n.family2_reference_relationship',
                        'n.guarantor_document_number',
                        'n.guarantor_district',
                        'n.guarantor_occupation',
                        'n.guarantor_name',
                        'n.guarantor_address',
                        'n.guarantor_phone',
                        'n.guarantor_relationship',
                        'n.extra_reference',
                        'n.period',
                        'n.quantity',
                        'n.visit_end_date',
                        'n.account_type',
                        'n.account_number',
                        'n.account_type_third',
                        'n.account_number_third',
                        'n.account_name_third',
                        'n.type_cv',
                        'n.has_letter',
                        'n.who_received_letter',
                        'n.date_received_letter',
                        'n.who_returned_letter',
                        'n.date_returned_letter',
                        'n.score',
                        'n.score_observation',
                        'n.account_active',
                    )
                    ->leftJoin('yards as y', 'n.sector', 'y.id')
                    ->leftJoin('zones as z', 'y.zone', 'z.id')
                    ->leftJoin('users as u', 'n.user_send', 'u.id')
                    ->leftJoin('districts as d', 'n.district', 'd.id')
                    ->leftJoin('districts as dh', 'n.address_house_district', 'dh.id')
                    ->leftJoin('districts as dw', 'n.address_work_district', 'dw.id')
                    ->when($status !== 'all', function ($q) use ($explodeStatus) {
                        return $q->whereIn('n.status', $explodeStatus);
                    })
                    ->orderBy('d.group', 'ASC')
                    ->orderBy('d.order', 'ASC')
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

        function listReds(int $city, int $user) {
            try {
              
                $sql = "SELECT 
                        ROW_NUMBER() OVER (ORDER BY 
                            CAST(SUBSTRING_INDEX(districts.order, ' ', 1) AS UNSIGNED) ASC, 
                            SUBSTRING_INDEX(districts.order, ' ', -1) ASC, 
                            lendings.id ASC
                        ) AS 'order',
                        lendings.id AS lending_id,
                        lendings.amount,
                        lendings.percentage,
                        lendings.has_double_interest,
                        lendings.status,
                        listings.name as listing_name,
                        listings.id as listing_id,
                        COALESCE(SUM(payments.amount), 0) AS total_paid,
                        news.id AS news_id,
                        news.name AS news_name,
                        news.status AS news_status,
                        news.observation AS news_observation,
                        news.type_cv AS news_type_cv,
                        lendings.firstDate,
                        lendings.endDate,
                        districts.name AS district_name,
                        yards.name AS sector_name,
                        yards.id AS sector_id,
                        yards.code AS sector_code,
                        zones.name AS city_name,
                        zones.id AS city_id,
                        DATEDIFF(CURRENT_DATE, lendings.firstDate) AS days_since_creation,
                        (lendings.amount * (1 + lendings.percentage / 100)) AS total_value, 
                        (lendings.amount * (1 + 
                            CASE 
                                WHEN lendings.has_double_interest = 1 THEN lendings.percentage * 2 / 100
                                ELSE lendings.percentage / 100
                            END
                        )) AS total_due, 
                        (lendings.amount * (1 + 
                            CASE 
                                WHEN lendings.has_double_interest = 1 THEN lendings.percentage * 2 / 100
                                ELSE lendings.percentage / 100
                            END
                        ) - COALESCE(SUM(payments.amount), 0)) AS remaining_balance,
                        address_data.address_type,
                        address_data.address_name,
                        address_data.address,
                        address_data.district,
                        address_data.address_latitude,
                        address_data.address_longitude,
                        districts.order AS district_order,
                        redcollectors.collector_id AS collector_id,
                        users.name AS collector_name,
                        (SELECT id 
                            FROM reddirections
                            WHERE address = address_data.address
                            AND type_ref = address_data.address_type
                            AND status IN ('creado', 'activo')
                            ORDER BY registered_date DESC
                            LIMIT 1
                        ) AS is_current
                    FROM 
                        lendings
                    LEFT JOIN 
                        listings ON lendings.listing_id = listings.id
                    LEFT JOIN 
                        payments ON lendings.id = payments.lending_id
                    LEFT JOIN 
                        news ON lendings.new_id = news.id
                    LEFT JOIN (
                        SELECT 
                            news.id as new_id,
                            'CASA' AS address_type,
                            'CASA' AS address_name,
                            address_house AS address,
                            address_house_district AS district,
                        	(SELECT latitude FROM files WHERE model_name = 'news' AND model_id = news.id AND name = 'PDF_CV') AS address_latitude,
                        	(SELECT longitude FROM files WHERE model_name = 'news' AND model_id = news.id AND name = 'PDF_CV') AS address_longitude
                        FROM news
                        WHERE address_house IS NOT NULL AND address_house_district IS NOT NULL
                        UNION ALL
                        SELECT 
                            news.id as new_id,
                            'TRABAJO' AS address_type,
                            'TRABAJO' AS address_name,
                            address_work,
                            address_work_district,
                        	(SELECT latitude FROM files WHERE model_name = 'news' AND model_id = news.id AND name = 'PDF_CV') AS address_latitude,
                        	(SELECT longitude FROM files WHERE model_name = 'news' AND model_id = news.id AND name = 'PDF_CV') AS address_longitude
                        FROM news
                        WHERE address_work IS NOT NULL AND address_work_district IS NOT NULL
                        UNION ALL
                        SELECT 
                            news.id as new_id,
                            'REF 1' AS address_type,
                            CONCAT(family_reference_name, ' | ', family_reference_relationship) AS address_name,
                            family_reference_address,
                            family_reference_district,
                        	(SELECT latitude FROM files WHERE model_name = 'news' AND model_id = news.id AND name = 'PDF_CV') AS address_latitude,
                        	(SELECT longitude FROM files WHERE model_name = 'news' AND model_id = news.id AND name = 'PDF_CV') AS address_longitude
                        FROM news
                        WHERE family_reference_address IS NOT NULL AND family_reference_district IS NOT NULL
                        UNION ALL
                        SELECT 
                            news.id as new_id,
                            'REF 2' AS address_type,
                            CONCAT(family2_reference_name, ' | ', family2_reference_relationship) AS address_name,
                            family2_reference_address,
                            family2_reference_district,
                        	(SELECT latitude FROM files WHERE model_name = 'news' AND model_id = news.id AND name = 'PDF_CV') AS address_latitude,
                        	(SELECT longitude FROM files WHERE model_name = 'news' AND model_id = news.id AND name = 'PDF_CV') AS address_longitude
                        FROM news
                        WHERE family2_reference_address IS NOT NULL AND family2_reference_district IS NOT NULL
                        UNION ALL
                        SELECT 
                            news.id as new_id,
                            'FIADOR' AS address_type,
                            CONCAT(guarantor_name, ' | ', guarantor_relationship) AS address_name,
                            guarantor_address,
                            guarantor_district,
                        	(SELECT latitude FROM files WHERE model_name = 'news' AND model_id = news.id AND name = 'PDF_CV') AS address_latitude,
                        	(SELECT longitude FROM files WHERE model_name = 'news' AND model_id = news.id AND name = 'PDF_CV') AS address_longitude
                        FROM news
                        WHERE guarantor_address IS NOT NULL AND guarantor_district IS NOT NULL
                    ) AS address_data ON news.id = address_data.new_id
                    LEFT JOIN 
                        districts ON address_data.district = districts.id
                    LEFT JOIN 
                        yards ON districts.sector = yards.id
                    LEFT JOIN 
                        zones ON zones.id = yards.zone
                    LEFT JOIN 
                        redcollectors ON redcollectors.sector_id = yards.id
                    LEFT JOIN 
                        users ON redcollectors.collector_id = users.id
                    WHERE 
                        lendings.status = 'open'
                        AND news.status = 'consignado'";
                        
                if ($city && $city > 0) {
                    $sql .= " AND zones.id = ".$city;
                }

                if ($user && $user > 0) {
                    $sql .= " AND redcollectors.collector_id = ".$user;
                }

                $sql .= " GROUP BY 
                        lendings.id, news.id, districts.id, address_data.address_type, address_data.address, address_data.district
                    HAVING 
                        days_since_creation > 19
                    ORDER BY 
                        CAST(SUBSTRING_INDEX(districts.order, ' ', 1) AS UNSIGNED) ASC, 
                        SUBSTRING_INDEX(districts.order, ' ', -1) ASC, 
                        lendings.id ASC;";

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
                            'text' => 'Se ha presentado un error al cargar los registros',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function create(array $novel){
            try {
                /* $validation = $this->validate($this->validator, $novel, null, 'registrar', 'nuevo', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                } */
                $message = 'Nuevo registrado con éxito';
                $new = $this->novel->from('news as n')->select('n.*')->where('n.phone', $novel['phone'])->first();

                DB::transaction(function () use ($novel, $new, &$message) {
                    $sql = $this->novel::create([
                        'document_number' => null,
                        'name' => $novel['name'],
                        'phone' => $novel['phone'],
                        'address' => $novel['address'],
                        'sector' => $novel['sector'],
                        'district' => $novel['district'],
                        'occupation' => $novel['occupation'],
                        'observation' => $novel['observation'],
                        'user_send' => $novel['userSend'],
                        'status' => $new ? 'analizando' : $novel['status'],
                        'account_type' => 'nequi',
                        'account_number' => $novel['phone'],
                    ]);

                    if ($new) {
                        $message = 'Ya existe un registro de cliente con el número de telefono ingresado.';
                        $question = Question::create([
                            'model_id' => $sql->id,
                            'model_name' => 'news',
                            'type' => 'nuevo',
                            'status' => 'pendiente',
                            'observation' => 'El numero de telefono '.$novel['phone'].' ya está registrado para otro cliente llamado: '.$new['name'].', con numero de documento: '.$new['document_number'],
                            'area_id' => 3,
                            'registered_by' => $novel['registered_by'],
                        ]);
                    }
    
                });
                return response()->json([
                    'message' => [
                        [
                            'text' => $message,
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

        function update(array $novel, int $id){
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
        
        function updateStatus(array $novel, int $id){
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
                        // $sql->document_number = $novel['documentNumber'];
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
                $sql = $this->novel->from('news as n')
                    ->select(
                        'n.id',
                        'n.document_number as documentNumber',
                        'n.name as name',
                        'n.phone as phone',
                        'n.address as address',
                        'n.address_house',
                        'n.address_house_district',
                        'dh.name as districtHouseName',
                        'n.address_work',
                        'n.address_work_district',
                        'dw.name as districtWorkName',
                        'n.site_visit',
                        'n.type_house',
                        'n.type_work',
                        'n.quantity',
                        'n.district as district',
                        'n.occupation as occupation',
                        'n.attempts as attempts',
                        'n.observation as observation',
                        'n.status as status',
                        'n.created_at as date',
                        'n.visit_start_date',
                        DB::Raw('IF(y.zone IS NOT NULL, z.name, "Sin ciudad") as cityName'),
                        DB::Raw('IF(y.zone IS NOT NULL, z.id, null) as city'),
                        DB::Raw('IF(n.sector IS NOT NULL, y.name, "Sin sector") as sectorName'),
                        DB::Raw('IF(n.sector IS NOT NULL, y.id, null) as sector'),
                        DB::Raw('IF(n.user_send IS NOT NULL, u.name, "Ninguno") as userSendName'),
                        DB::Raw('IF(n.user_send IS NOT NULL, u.id, null) as userSend'),
                        DB::Raw('IF(us.id IS NOT NULL, us.id, null) as userVisit'),
                        DB::Raw('IF(us.id IS NOT NULL, us.push_token, null) as userVisitToken'),
                        'n.family_reference_district',
                        'drf.name as family_reference_district_name',
                        'n.family_reference_name',
                        'n.family_reference_address',
                        'n.family_reference_phone',
                        'n.family_reference_relationship',
                        'n.family2_reference_district',
                        'drf2.name as family2_reference_district_name',
                        'n.family2_reference_name',
                        'n.family2_reference_address',
                        'n.family2_reference_phone',
                        'n.family2_reference_relationship',
                        'n.guarantor_district',
                        'dg.name as guarantor_district_name',
                        'n.guarantor_document_number',
                        'n.guarantor_name',
                        'n.guarantor_address',
                        'n.guarantor_phone',
                        'n.guarantor_occupation',
                        'n.guarantor_relationship',
                        'n.extra_reference',
                        'n.period',
                        'n.quantity',
                        'd.id as diary_id',
                        'd.status as diary_status',
                        'y.id as sector',
                        'z.id as city',
                        'yh.id as sectorHouse',
                        'zh.id as cityHouse',
                        'yw.id as sectorWork',
                        'zw.id as cityWork',
                        'yrf.id as sectorRef1',
                        'zrf.id as cityRef1',
                        'yrf2.id as sectorRef2',
                        'zrf2.id as cityRef2',
                        'yg.id as sectorGuarantor',
                        'zg.id as cityGuarantor',
                        'n.visit_end_date',
                        'n.account_type',
                        'n.account_number',
                        'n.account_type_third',
                        'n.account_number_third',
                        'n.account_name_third',
                        'n.type_cv',
                        'n.lent_by',
                        'ul.name as lent_by_name',
                        'ua.name as approved_by_name',
                        'n.has_letter',
                        'n.who_received_letter',
                        'n.date_received_letter',
                        'n.who_returned_letter',
                        'n.date_returned_letter',
                        'n.score',
                        'n.score_observation',
                        'n.account_active',
                        'li.name as list_name',
                        'li.id as list_id',
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
                    ->leftJoin('lendings as l', 'l.new_id', 'n.id')
                    ->leftJoin('listings as li', 'li.id', 'l.listing_id')
                    ->leftJoin('users as ul', 'n.lent_by', 'ul.id')
                    ->leftJoin('users as ua', 'n.approved_by', 'ua.id')
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

        function getByPhone(string $phone){
            try {
                $new = $this->novel->from('news as n')->select('n.*')->where('n.phone', $phone)->first();
                return response()->json([
                    'data' => $new
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