<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\DeliveryServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Delivery;
    use App\Traits\Commons;
    use Illuminate\Support\Facades\DB;

    class DeliveryServiceImplement implements DeliveryServiceInterface {

        use Commons;

        private $delivery;
        private $validator;

        function __construct(){
            $this->delivery = new Delivery;
        }

        function create(array $delivery){
            try {
                DB::transaction(function () use ($delivery) {
                    $status = $this->delivery::create([
                        'listing_id' => $delivery['listing_id'],
                        'capital' => $delivery['capital'],
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
                            'detail' => $e->getMessage(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>
