<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Lending;
use App\Models\Payment;
use App\Models\Expense;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $idUserSesion = $request->user()->id;
            $date = date("Y-m-d");
            
            $items = Listing::selectRaw('
                listings.*, 
                zones.name as city_name, 
                files1.url as capture_delivery_file, 
                files2.url as capture_route_file
            ')
            ->leftJoin('files as files1', function ($join) use ($date) {
                $join->on('files1.model_id', '=', 'listings.id')
                    ->where('files1.model_name', '=', 'listings')
                    ->where('files1.name', '=', 'CAPTURE_DELIVERY')
                    ->whereRaw('files1.created_at = (
                        SELECT MAX(created_at) 
                        FROM files 
                        WHERE files.model_id = listings.id
                        AND files.model_name = "listings"
                        AND files.name = "CAPTURE_DELIVERY"
                        AND files.created_at BETWEEN "'.$date.' 00:00:00" AND "'.$date.' 23:59:59"
                    )');
            })
            ->leftJoin('files as files2', function ($join) use ($date) {
                $join->on('files2.model_id', '=', 'listings.id')
                    ->where('files2.model_name', '=', 'listings')
                    ->where('files2.name', '=', 'CAPTURE_ROUTE')
                    ->whereRaw('files2.created_at = (
                        SELECT MAX(created_at) 
                        FROM files 
                        WHERE files.model_id = listings.id 
                        AND files.model_name = "listings" 
                        AND files.name = "CAPTURE_ROUTE"
                        AND files.created_at BETWEEN "'.$date.' 00:00:00" AND "'.$date.' 23:59:59"
                    )');
            })
            ->leftJoin('zones', 'zones.id', '=', 'listings.city_id')
            ->with('userCollector')
            ->with('userLeader')
            ->with('userAuthorized')
            ->where('listings.status', '=', 'activa')
            ->get();

        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $items,
            'message' => 'Succeed',
        ], JsonResponse::HTTP_OK);
    }
    
    public function getMine(Request $request)
    {
        try {
            $idUserSesion = $request->user()->id;
            $items = Listing::where('status', '=', 'activa')
                            ->where(function ($query) use ($idUserSesion) {
                                $query->where('user_id_collector', '=', $idUserSesion);
                            })
                            ->with('userCollector')
                            ->with('userLeader')
                            ->with('userAuthorized')
                            ->get();

        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $items,
            'message' => 'Succeed',
        ], JsonResponse::HTTP_OK);
    }

    public function show(Request $request, $id)
    {
        try {
            $date = date("Y-m-d");

            $item = Listing::selectRaw('
                listings.*, 
                files1.url as capture_delivery_file, 
                files2.url as capture_route_file
            ')
            ->leftJoin('files as files1', function ($join) use ($date) {
                $join->on('files1.model_id', '=', 'listings.id')
                    ->where('files1.model_name', '=', 'listings')
                    ->where('files1.name', '=', 'CAPTURE_DELIVERY')
                    ->whereRaw('files1.created_at = (
                        SELECT MAX(created_at) 
                        FROM files 
                        WHERE files.model_id = listings.id
                        AND files.model_name = "listings"
                        AND files.name = "CAPTURE_DELIVERY"
                        AND files.created_at BETWEEN "'.$date.' 00:00:00" AND "'.$date.' 23:59:59"
                    )');
            })
            ->leftJoin('files as files2', function ($join) use ($date) {
                $join->on('files2.model_id', '=', 'listings.id')
                    ->where('files2.model_name', '=', 'listings')
                    ->where('files2.name', '=', 'CAPTURE_ROUTE')
                    ->whereRaw('files2.created_at = (
                        SELECT MAX(created_at) 
                        FROM files 
                        WHERE files.model_id = listings.id 
                        AND files.model_name = "listings" 
                        AND files.name = "CAPTURE_ROUTE"
                        AND files.created_at BETWEEN "'.$date.' 00:00:00" AND "'.$date.' 23:59:59"
                    )');
            })
            ->with('userCollector')
            ->with('userLeader')
            ->with('userAuthorized')
            ->with('lendings')
            ->where('listings.id', $id)
            ->first();
            
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $item,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function store(Request $request)
    {
        try {
            $idUserSesion = $request->user()->id;
            
            $item = Listing::create([
                'name' => $request->name,
                'status' => $request->status,
                'user_id_collector' => $request->user_id_collector,
                'user_id_leader' => $request->user_id_leader,
                'user_id_authorized' => $request->user_id_authorized,
                'user_id' => $idUserSesion,
                'city_id' => $request->city_id,
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => [
                [
                    'text' => 'Creado con éxito.',
                    'detail' => null,
                ]
            ]
        ], JsonResponse::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        try {
            $items = Listing::find($id)
                        ->update($request->all());
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => [
                [
                    'text' => 'Modificado con éxito.',
                    'detail' => null,
                ]
            ]
        ], JsonResponse::HTTP_OK);
    }

    public function destroy(Request $request, $id)
    {
        try {
            $items = Listing::destroy($id);
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => [
                [
                    'text' => 'Eliminado con éxito.',
                    'detail' => null,
                ]
            ]
        ], JsonResponse::HTTP_OK);
    }

    public function getDelivery(Request $request, $idList, $date)
    {
        $data = null;
        try {
            $idUserSesion = $request->user()->id;

            $itemList = Listing::find($idList);

            $itemPayment = Payment::selectRaw('
                    COUNT(*) as total_count, 
                    COALESCE(SUM(payments.amount), 0) as total_amount, 
                    COUNT(DISTINCT lendings.id) as total_clients,
                    COALESCE(SUM(CASE WHEN payments.type = "nequi" AND payments.observation <> "adelanto" THEN payments.amount ELSE 0 END), 0) as total_amount_nequi,
                    COALESCE(SUM(CASE WHEN payments.type = "nequi" AND payments.observation = "adelanto" THEN payments.amount ELSE 0 END), 0) as total_amount_repayment,
                    COALESCE(SUM(CASE WHEN payments.type = "articulo" THEN payments.amount ELSE 0 END), 0) as total_amount_article,
                    COUNT(CASE WHEN payments.type = "nequi" AND payments.observation <> "adelanto" THEN 1 ELSE NULL END) as total_count_nequi,
                    COUNT(CASE WHEN payments.type = "nequi" AND payments.observation = "adelanto" THEN 1 ELSE NULL END) as total_count_repayment,
                    COUNT(CASE WHEN payments.type = "articulo" THEN 1 ELSE NULL END) as total_count_article,
                    COALESCE(SUM(CASE WHEN payments.is_street = 0 AND (payments.type = "nequi" OR payments.type = "renovacion") THEN payments.amount ELSE 0 END), 0) as total_amount_secre,
                    COALESCE(SUM(CASE WHEN payments.is_street = 1 AND payments.type = "nequi" THEN payments.amount ELSE 0 END), 0) as total_amount_street')
                ->join('lendings', 'lendings.id', '=', 'payments.lending_id')
                ->whereBetween('payments.date', [$date." 00:00:00", $date." 23:59:59"])
                ->where('lendings.listing_id', $idList)
                ->where('payments.is_valid', 1)
                ->first();

            $itemRenove = Lending::selectRaw('COUNT(*) as total_count, COALESCE(SUM(amount), 0) as total_amount')
                        ->whereBetween('created_at', [$date." 00:00:00", $date." 23:59:59"])
                        ->where('listing_id', $idList)
                        ->where('status', 'open')
                        ->where('type', 'R')
                        ->first();

            $itemNovel = Lending::selectRaw('COUNT(*) as total_count, COALESCE(SUM(amount), 0) as total_amount')
                        ->whereBetween('created_at', [$date." 00:00:00", $date." 23:59:59"])
                        ->where('listing_id', $idList)
                        ->where('status', 'open')
                        ->where('type', 'N')
                        ->first();
                        
            $itemExpense = Expense::selectRaw('COUNT(*) as total_count_renovation, COALESCE(SUM(expenses.amount), 0) as total_amount_renovation')
                        ->leftJoin('lendings', 'lendings.expense_id', '=', 'expenses.id')
                        ->join('listings', 'listings.id', '=', 'lendings.listing_id')
                        ->leftJoin('files', function($join) {
                            $join->on('files.model_id', '=', 'expenses.id')
                                 ->where('files.model_name', '=', 'expenses');
                        })
                        ->whereBetween('expenses.created_at', [$date." 00:00:00", $date." 23:59:59"])
                        ->where('expenses.item_id', 1)
                        ->where('expenses.status', 'aprobado')
                        ->whereNotNull('files.id')
                        ->where('listings.id', $idList)
                        ->first();
                        
            $data = [
                'itemList' => $itemList,
                'itemPayment' => $itemPayment,
                'itemRenove' => $itemRenove,
                'itemNovel' => $itemNovel,
                'itemExpense' => $itemExpense,
                'date' => $date,
            ];

        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $data,
        ], JsonResponse::HTTP_OK);
    }

    public function listWithDeliveries(Request $request, $date)
    {
        $items = [];
        try {
            $idUserSesion = $request->user()->id;

            $items = Listing::selectRaw('
                listings.*, 
                files1.url as capture_delivery_file, 
                files2.url as capture_route_file
            ')
            ->leftJoin('files as files1', function ($join) use ($date) {
                $join->on('files1.model_id', '=', 'listings.id')
                    ->where('files1.model_name', '=', 'listings')
                    ->where('files1.name', '=', 'CAPTURE_DELIVERY')
                    ->whereRaw('files1.created_at = (
                        SELECT MAX(created_at) 
                        FROM files 
                        WHERE files.model_id = listings.id
                        AND files.model_name = "listings"
                        AND files.name = "CAPTURE_DELIVERY"
                        AND files.created_at BETWEEN "'.$date.' 00:00:00" AND "'.$date.' 23:59:59"
                    )');
            })
            ->leftJoin('files as files2', function ($join) use ($date) {
                $join->on('files2.model_id', '=', 'listings.id')
                    ->where('files2.model_name', '=', 'listings')
                    ->where('files2.name', '=', 'CAPTURE_ROUTE')
                    ->whereRaw('files2.created_at = (
                        SELECT MAX(created_at) 
                        FROM files 
                        WHERE files.model_id = listings.id 
                        AND files.model_name = "listings" 
                        AND files.name = "CAPTURE_ROUTE"
                        AND files.created_at BETWEEN "'.$date.' 00:00:00" AND "'.$date.' 23:59:59"
                    )');
            })
            ->get();
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $items,
        ], JsonResponse::HTTP_OK);
    }

    public function getInfo(Request $request, $idList)
    {
        $data = null;
        try {
            $idUserSesion = $request->user()->id;

            $queryYellowDown = DB::table(function ($subquery) {
                $subquery
                    ->select(
                        'lendings.id AS lending_id',
                        'lendings.nameDebtor AS cliente',
                        'listings.name AS ruta',
                        'listings.id AS ruta_id',
                        DB::raw('((lendings.amount * (1 + 
                            CASE 
                                WHEN lendings.has_double_interest = 1 THEN lendings.percentage * 2 / 100
                                ELSE lendings.percentage / 100
                            END
                        )) - COALESCE(SUM(payments.amount), 0)) AS pendiente'),
                        DB::raw('DATEDIFF(CURRENT_DATE, lendings.firstDate) AS dias'),
                        DB::raw('(lendings.amount * (lendings.percentage / 100)) AS interes'),
                        DB::raw('COALESCE(SUM(payments.amount), 0) AS pagado')
                    )
                    ->from('lendings')
                    ->leftJoin('payments', 'lendings.id', '=', 'payments.lending_id')
                    ->leftJoin('listings', 'listings.id', '=', 'lendings.listing_id')
                    ->where('lendings.status', 'open')
                    ->where('listings.id', $idList)
                    ->whereRaw('DATEDIFF(CURRENT_DATE, lendings.firstDate) >= 8')
                    ->whereRaw('DATEDIFF(CURRENT_DATE, lendings.firstDate) <= 15')
                    ->groupBy('lendings.id', 'listings.id')
                    ->havingRaw('interes >= pagado');
            })->selectRaw('COUNT(*) AS total')
                ->orderBy('ruta_id')
                ->orderBy('lending_id');
            
            $totalCountYellowDown = $queryYellowDown->value('total');

            $data = [
                'totalCountYellowDown' => $totalCountYellowDown,
            ];

        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $data,
        ], JsonResponse::HTTP_OK);
    }

}