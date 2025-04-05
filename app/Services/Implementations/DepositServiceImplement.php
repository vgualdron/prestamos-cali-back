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
