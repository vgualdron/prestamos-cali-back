<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\ExpenseServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Expense;
    use App\Validator\{ExpenseValidator, ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;

    class ExpenseServiceImplement implements ExpenseServiceInterface {

        use Commons;

        private $expense;
        private $validator;
        private $profileValidator;

        function __construct(ExpenseValidator $validator, ProfileValidator $profileValidator){
            $this->expense = new Expense;
            $this->validator = $validator;
            $this->profileValidator = $profileValidator;
        }

        function list(string $status, string $items) {
            try {
                $explodeStatus = explode(',', $status);
                $explodeItems = explode(',', $items);
                $sql = $this->expense->from('expenses as e')
                    ->select(
                        'e.*',
                        'a.id as area_id',
                        'a.name as area_name',
                        'i.id as item_id',
                        'i.name as item_name',
                        'u.name as user_name',
                        'f.url as file_url',
                    )
                    ->leftJoin('items as i', 'e.item_id', 'i.id')
                    ->leftJoin('areas as a', 'i.area_id', 'a.id')
                    ->leftJoin('users as u', 'e.user_id', 'u.id')
                    ->leftJoin('files as f', 'f.id', 'e.file_id')
                    ->when($status !== 'all', function ($q) use ($explodeStatus) {
                        return $q->whereIn('e.status', $explodeStatus);
                    })
                    ->when($items !== 'all', function ($q) use ($explodeItems) {
                        return $q->whereNotIn('e.item_id', $explodeItems);
                    })
                    ->orderBy('e.date', 'ASC')
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

        function listByUser(int $user, string $status, string $items) {
            try {
                $explodeStatus = explode(',', $status);
                $explodeItems = explode(',', $items);
                $sql = $this->expense->from('expenses as e')
                    ->select(
                        'e.*',
                        'a.id as area_id',
                        'a.name as area_name',
                        'i.id as item_id',
                        'i.name as item_name',
                        'u.name as user_name',
                        'f.url as file_url',
                    )
                    ->leftJoin('items as i', 'e.item_id', 'i.id')
                    ->leftJoin('areas as a', 'i.area_id', 'a.id')
                    ->leftJoin('users as u', 'e.user_id', 'u.id')
                    ->leftJoin('files as f', 'f.id', 'e.file_id')
                    ->where('e.user_id', $user)
                    ->when($status !== 'all', function ($q) use ($explodeStatus) {
                        return $q->whereIn('e.status', $explodeStatus);
                    })
                    ->when($items !== 'all', function ($q) use ($explodeItems) {
                        return $q->whereNotIn('e.item_id', $explodeItems);
                    })
                    ->orderBy('e.date', 'DESC')
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

        function listByItem(string $status, int $item) {
            try {
                $explodeStatus = explode(',', $status);
                $sql = $this->expense->from('expenses as e')
                    ->select(
                        'e.*',
                        'a.id as area_id',
                        'a.name as area_name',
                        'i.id as item_id',
                        'i.name as item_name',
                        'u.name as user_name',
                        'n.id as new_id',
                        'n.name as new_name',
                        'n.type_cv as new_type_cv',
                        'n.account_active',
                        'n.account_type',
                        'n.account_number',
                        'n.account_type_third',
                        'n.account_number_third',
                        'n.account_name_third',
                        'ls.name as listing_name',
                        'f.url as file_url',
                    )
                    ->leftJoin('items as i', 'e.item_id', 'i.id')
                    ->leftJoin('areas as a', 'i.area_id', 'a.id')
                    ->leftJoin('users as u', 'e.user_id', 'u.id')
                    ->leftJoin('lendings as l', 'l.expense_id', 'e.id')
                    ->leftJoin('news as n', 'n.id', 'l.new_id')
                    ->leftJoin('listings as ls', 'ls.id', 'l.listing_id')
                    ->leftJoin('files as f', 'f.id', 'e.file_id')
                    ->where('e.item_id', $item)
                    ->when($status !== 'all', function ($q) use ($explodeStatus) {
                        return $q->whereIn('e.status', $explodeStatus);
                    })
                    ->distinct()
                    ->orderBy('e.date', 'ASC')
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

        function create(array $expense){
            try {
                DB::transaction(function () use ($expense) {
                    $sql = $this->expense::create([
                        'date' => $expense['date'],
                        'amount' => $expense['amount'],
                        'status' => $expense['status'],
                        'description' => $expense['description'],
                        'item_id' => $expense['item_id'],
                        'user_id' => $expense['user_id'] > 0 ? $expense['user_id'] : null,
                    ]);

                });
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Nuevo registrado con éxito',
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

        function update(array $expense, int $id){
            try {
                $sql = $this->expense::find($id)->update($expense);
            } catch (Exception $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'message' => [
                    [
                        'text' => 'Modificado con éxito.',
                        'detail' => null,
                    ]
                ]
            ], Response::HTTP_OK);
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
                $sql = $this->expense->from('news as n')
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
                        'n.guarantor_name',
                        'n.guarantor_address',
                        'n.guarantor_phone',
                        'n.guarantor_relationship',
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

    }
?>
