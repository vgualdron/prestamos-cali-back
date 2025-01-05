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
                        'created_at' => $delivery['date'],
                        'capital' => $delivery['capital'],
                        'transfers_count' => $delivery['transfers_count'],
                        'transfers_amount' => $delivery['transfers_amount'],
                        'advances_count' => $delivery['advances_count'],
                        'advances_amount' => $delivery['advances_amount'],
                        'articles_count' => $delivery['articles_count'],
                        'articles_amount' => $delivery['articles_amount'],
                        'renovations_count' => $delivery['renovations_count'],
                        'renovations_amount' => $delivery['renovations_amount'],
                        'expenses_news_count' => $delivery['expenses_news_count'],
                        'expenses_news_amount' => $delivery['expenses_news_amount'],
                        'expenses_renovations_count' => $delivery['expenses_renovations_count'],
                        'expenses_renovations_amount' => $delivery['expenses_renovations_amount'],
                        'collection_secre' => $delivery['collection_secre'],
                        'collection_street' => $delivery['collection_street'],
                        'clients' => $delivery['clients'],
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
