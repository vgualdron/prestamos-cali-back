<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\DepositServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Deposit;
    use App\Validator\{ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;

    class DepositServiceImplement implements DepositServiceInterface {

        use Commons;

        private $deposit;
        private $validator;
        private $profileValidator;

        function __construct(ProfileValidator $profileValidator){
            $this->deposit = new Deposit;
            $this->profileValidator = $profileValidator;
        }


        function list(string $status) {
            try {
                $explodeStatus = explode(',', $status);
                $sql = $this->deposit
                        ->from('deposits as d')
                        ->select(
                            'l.*',
                            'd.amount as deposit_amount',
                            'd.status as deposit_status',
                            'd.file_id as deposit_file_id',
                            'd.created_at as deposit_created_at',
                            'u.name as user_name',
                            'a.name as area_name',
                            'u.area',
                            DB::raw('(SELECT COALESCE(SUM(amount), 0) FROM deposits d WHERE loan_id = l.id AND d.status = "aprobado") as total_paid'),
                            DB::raw('(l.amount - (SELECT COALESCE(SUM(amount), 0) FROM deposits d WHERE loan_id = l.id AND d.status = "aprobado")) as remaining')
                        )
                        ->join('loans as l', 'l.id', '=', 'd.loan_id')
                        ->leftJoin('users as u', 'l.user_id', '=', 'u.id')
                        ->leftJoin('areas as a', 'u.area', '=', 'a.id')
                        ->when($status !== 'all', function ($query) use ($explodeStatus) {
                            return $query->whereIn('l.status', $explodeStatus);
                        })
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

        function create(array $deposit){
            try {
                $sql = $this->deposit::create([
                    'loan_id' => $deposit['loan_id'],
                    'amount' => $deposit['amount'],
                    'status' => $deposit['status'],
                    'date_transaction' => $deposit['date'],
                    'file_id' => $deposit['file_id'],
                    'type' => 'nequi',
                    'observation' => $deposit['observation'],
                    'reference' => null,
                    'nequi' => null,
                ]);
                return response()->json([
                    'data' => $sql,
                    'message' => 'Succeed'
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

        function update(array $deposit, int $id){
            try {
                $sql = $this->deposit::find($id);
                if(!empty($sql)) {
                    DB::transaction(function () use ($sql, $deposit) {
                        $sql->amount = $deposit['amount'];
                        $sql->status = $deposit['status'];
                        $sql->observation = $deposit['observation'];
                        $sql->file_id = $deposit['file_id'];
                        $sql->type = $deposit['type'];
                        $sql->reference = $deposit['reference'];
                        $sql->nequi = $deposit['nequi'];
                        $sql->date_transaction = $deposit['date_transaction'];
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
                $sql = $this->deposit::find($id);
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

    }
?>
