<?php

namespace App\Http\Controllers;

use App\Models\Lending;
use App\Models\Payment;
use App\Models\Interest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LendingController extends Controller
{
    public function index(Request $request, $idList)
    {
        try {
            $idUserSesion = $request->user()->id;
            $items = Lending::where('listing_id', '=', $idList)
                                ->with('payments')
                                ->with('interests')
                                ->where('status', '=', 'open')
                                ->orderBy('order', 'asc')->get();
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
    
    public function getLendingsWithPaymentsCurrentDate(Request $request, $idList)
    {
        try {
            $idUserSesion = $request->user()->id;
            $items = Lending::select('lendings.*')
                                ->leftjoin('payments', 'lendings.id', 'payments.lending_id')
                                ->leftjoin('interests', 'lendings.id', 'interests.lending_id')
                                ->with('payments')
                                ->with('interests')
                                ->where('listing_id', '=', $idList)
                                // ->where('payments.date', '<=', date("Y-m-d h:i:s"))
                                // ->where('payments.amount', '=', NULL)
                                ->where('lendings.status', '=', 'open')
                                ->distinct()
                                ->orderBy('lendings.order', 'asc')->get();
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
    
    public function getLendingsFromListCurrentDate(Request $request, $idList)
    {
        $date = date("Y-m-d");
        $firstDate = date("Y-m-d H:i:s", (strtotime(date($date))));
        $endDate = date("Y-m-d H:i:s", (strtotime(date($date)) + 86399));
        
        try {
            $idUserSesion = $request->user()->id;
            $items = Lending::where('listing_id', '=', $idList)
                                ->whereBetween('created_at', [$firstDate, $endDate])
                                ->distinct()
                                ->orderBy('lendings.order', 'asc')->get();
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
            $items = Lending::where('id', '=', $id)->with('payments')->with('interests')->first();
            
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
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function store(Request $request)
    {
        try {
            $idUserSesion = $request->user()->id;
            $period = $request->period;
            $countDays = '+1 days';
            $firstDate = date("Y-m-d", strtotime($request->firstDate));
            
            $item = Lending::create([
                'nameDebtor' => $request->nameDebtor,
                'address' => $request->address,
                'phone' => $request->phone,
                'firstDate' => $firstDate,
                'endDate' => $request->endDate,
                'amount' => $request->amount,
                'amountFees' => $request->amountFees,
                'percentage' => $request->percentage,
                'period' => $request->period,
                'order' => $request->order,
                'status' => $request->status,
                'listing_id' => $request->listing_id,
                'user_id' => $idUserSesion,
                'type' => $request->type,
            ]);
            
            if ($period === 'diario') {
                $countDays = '+1 days';
            } else if ($period === 'semanal') {
                $countDays = '+1 weeks';
            } else if ($period === 'quincenal') {
                $countDays = '+2 weeks';
            } else if ($period === 'mensual') {
                $countDays = '+1 months';
            }
            if ($request->type === 'normal') {
                for ($i = 0; $i < (int)$item->amountFees; $i++) {
                    $modDate = strtotime($firstDate.$countDays);
                    $newDate = date("Y-m-d", $modDate);
                    
                    $itemPayment = Payment::create([
                        'lending_id' => (int)$item->id,
                        'date' => $newDate,
                        'amount' => null,
                        'color' => ''
                    ]);
                    $firstDate = $newDate;
                }
            } else {
                for ($i = 0; $i < (int)$item->amountFees; $i++) {
                    $modDate = strtotime($firstDate.$countDays);
                    $newDate = date("Y-m-d", $modDate);
                    
                    if ($i === 0) {
                        $itemPayment = Payment::create([
                            'lending_id' => (int)$item->id,
                            'date' => $newDate,
                            'amount' => null,
                            'color' => ''
                        ]);
                    }
                    
                    $itemInterest = Interest::create([
                        'lending_id' => (int)$item->id,
                        'date' => $newDate,
                        'amount' => null,
                        'color' => ''
                    ]);
                    $firstDate = $newDate;
                }
                
            }
            
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

    public function update(Request $request, $id)
    {
        try {
            $items = Lending::find($id)
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
            'data' => $items,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }


    public function destroy(Request $request, $id)
    {
        try {
            $items = Lending::destroy($id);
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
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }
    
    public function updateOrderRows(Request $request)
    {
        try {
            $rows = $request->all();
            
            $items = [];
            $index = 1;
            foreach($rows['rows'] as $row){
                $item = Lending::find($row['id'])->update([
                    'order' => $index
                ]);
                
                $items[] = $item;
                $index++;
            }
         
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
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }
}