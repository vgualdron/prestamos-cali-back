<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DiscountController extends Controller
{
    public function store(Request $request)
    {
        try {
            $idUserSesion = $request->user()->id;

            $item = Discount::create([
                'lending_id' => $request->lending_id,
                'date' => $request->date,
                'amount' => $request->amount,
                'observation' => $request->observation ?? '',
                'status' => $request->status,
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
            'data' => $item,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function destroy(Request $request, $id)
    {
        try {
            $items = Discount::destroy($id);
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
