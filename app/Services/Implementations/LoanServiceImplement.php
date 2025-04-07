<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\LoanServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Loan;
    use App\Validator\{ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;

    class LoanServiceImplement implements LoanServiceInterface {

        use Commons;

        private $loan;
        private $validator;
        private $profileValidator;

        function __construct(ProfileValidator $profileValidator){
            $this->loan = new Loan;
            $this->profileValidator = $profileValidator;
        }

        function list(string $status) {
            try {
                $explodeStatus = explode(',', $status);
                $sql = $this->loan
                    ->from('loans as l')
                    ->select(
                        'l.*',
                        'u.name as user_name',
                        'a.name as area_name',
                        'u.area',
                        // Total abonado
                        DB::raw('(SELECT COALESCE(SUM(amount), 0) FROM deposits d WHERE d.loan_id = l.id AND d.status = "aprobado") as total_paid'),
                        // Cuánto queda debiendo
                        DB::raw('(l.amount - (SELECT COALESCE(SUM(amount), 0) FROM deposits d WHERE d.loan_id = l.id AND d.status = "aprobado")) as remaining'),
                        // Conteo de depósitos en estado aprobado
                        DB::raw('(SELECT COUNT(*) FROM deposits d WHERE d.loan_id = l.id AND d.status = "aprobado") as count_approved'),
                        // Conteo de depósitos en estado creado
                        DB::raw('(SELECT COUNT(*) FROM deposits d WHERE d.loan_id = l.id AND d.status = "creado") as count_created'),
                        // Conteo de depósitos en estado rechazado
                        DB::raw('(SELECT COUNT(*) FROM deposits d WHERE d.loan_id = l.id AND d.status = "rechazado") as count_rejected')
                    )
                    ->leftJoin('users as u', 'l.user_id', '=', 'u.id')
                    ->leftJoin('areas as a', 'u.area', '=', 'a.id')
                    ->when($status !== 'all', function ($query) use ($explodeStatus) {
                        return $query->whereIn('l.status', $explodeStatus);
                    })
                    ->with('deposits.file')
                    ->orderBy('u.area', 'desc')
                    ->orderBy('u.name', 'desc')
                    ->orderBy('l.created_at', 'desc')
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

        // TO DO NO SE USA
        function create(array $question){
            try {
                DB::transaction(function () use ($question) {
                    $value = $question['value'];
                    $sql = $this->question::create([
                        'model_id' => $question['model_id'],
                        'model_name' => $question['model_name'],
                        'type' => $question['type'],
                        'status' => $question['status'],
                        'observation' => $question['observation'],
                        'value' => $value ? $value : '',
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

        function update(array $loan, int $id){
            try {
                /* $validation = $this->validate($this->validator, $novel, $id, 'actualizar', 'nuevo', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                } */
                $sql = $this->loan::find($id);
                if(!empty($sql)) {
                    DB::transaction(function () use ($sql, $loan) {
                        $sql->amount = $loan['amount'];
                        $sql->fee = $loan['fee'];
                        $sql->status = $loan['status'];
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

        // TO DO NO SE USA
        function delete(int $id){
            try {
                $sql = $this->question::find($id);
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

        // TO DO NO SE USA
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

    }
?>
