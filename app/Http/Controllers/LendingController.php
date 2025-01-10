<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Lending;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\File;
use App\Models\Listing;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LendingController extends Controller
{
    public function index(Request $request, $idList)
    {
        try {
            $idUserSesion = $request->user()->id;
            $items = Lending::where('listing_id', '=', $idList)
                                ->with('payments')
                                ->with('discounts')
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
            $status1 = 'open';
            $status2 = 'renovated';
            $status3 = 'closed';
            $startDate = date('Y-m-d'.' 00:00:00');
            $endDate = date('Y-m-d'.' 23:59:59');
            $idUserSesion = $request->user()->id;

			$items = Lending::select([
                'lendings.*',
                'news.family_reference_name',
                'news.family_reference_phone',
                'news.family2_reference_name',
                'news.family2_reference_phone',
                'news.guarantor_name',
                'news.guarantor_phone',
                'news.has_letter',
                'news.account_type',
                'news.account_number',
                'news.account_type_third',
                'news.account_number_third',
                'news.account_name_third',
                'news.account_active',
                'files.id as file_id_r',
                'files.name as file_name_r',
                'files.url as file_url_r',
                'files.status as file_status_r',
                'f.id as file_id_n',
                'f.name as file_name_n',
                'f.url as file_url_n',
                'f.status as file_status_n',
                'filePdf.id as file_pdf_id',
                'filePdf.name as file_pdf_name',
                'filePdf.url as file_pdf_url',
                'filePdf.status as file_pdf_status',
                'expenses.status as expense_status',
            ])
            ->leftJoin('payments', 'lendings.id', '=', 'payments.lending_id')
            ->leftJoin('news', 'news.id', '=', 'lendings.new_id')
            ->leftJoin('expenses', 'expenses.id', '=', 'lendings.expense_id')
            ->leftJoin('files', function ($join) {
                $join->on('files.model_id', '=', 'lendings.expense_id')
                     ->where('files.model_name', '=', 'expenses');
            })
            ->leftJoin('files as f', function ($join) {
                $join->on('f.model_id', '=', 'news.id')
                     ->where('f.model_name', '=', 'news')
                     ->where('f.name', '=', 'FOTO_VOUCHER');
            })
            ->leftJoin('files as filePdf', function ($join) {
                $join->on('filePdf.model_id', '=', 'news.id')
                     ->where('filePdf.model_name', '=', 'news')
                     ->where('filePdf.name', '=', 'PDF_CV');
            })
            ->with('payments')
            ->with('discounts')
            ->with('reddirections')
            ->where(function ($query) use ($idList, $status1, $status2, $status3, $startDate, $endDate) {
                $query->where(function ($subQuery) use ($idList, $status1) {
                    $subQuery->where('listing_id', $idList)
                        ->whereIn('lendings.status', [$status1]);
                })
                ->orWhere(function ($subQuery) use ($idList, $status2, $status3, $startDate, $endDate) {
                    $subQuery->where('listing_id', $idList)
                        ->whereIn('lendings.status', [$status2, $status3])
                        ->whereBetween('lendings.updated_at', [$startDate, $endDate]);
                });
            })
            ->distinct()
            ->orderBy('lendings.firstDate', 'asc')
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

    public function getLendingsForSale(Request $request, $idList)
    {
        try {
            $status1 = 'open';
            $status2 = 'renovated';
            $status3 = 'closed';
            $idUserSesion = $request->user()->id;

			$items = Lending::select([
                'lendings.*',
                'news.family_reference_name',
                'news.family_reference_phone',
                'news.family2_reference_name',
                'news.family2_reference_phone',
                'news.guarantor_name',
                'news.guarantor_phone',
                'news.has_letter',
                'news.score',
                'news.score_observation',
                'files.id as file_id_r',
                'files.name as file_name_r',
                'files.url as file_url_r',
                'files.status as file_status_r',
                'f.id as file_id_n',
                'f.name as file_name_n',
                'f.url as file_url_n',
                'f.status as file_status_n',
                'filePdf.id as file_pdf_id',
                'filePdf.name as file_pdf_name',
                'filePdf.url as file_pdf_url',
                'filePdf.status as file_pdf_status',
                'expenses.status as expense_status',
                'listings.name as listing_name',
                'listings.user_id_collector as listing_user',
                'questions.status as question_status',
            ])
            ->join('listings', 'listings.id', '=', 'lendings.listing_id')
            ->leftJoin('payments', 'lendings.id', '=', 'payments.lending_id')
            ->leftJoin('news', 'news.id', '=', 'lendings.new_id')
            ->leftJoin('expenses', 'expenses.id', '=', 'lendings.expense_id')
            ->leftJoin('questions', function ($join) {
                $join->on('questions.model_id', '=', 'news.id')
                     ->where('questions.model_name', '=', 'news')
                     ->where('questions.status', '<>', 'rechazado')
                     ->where('questions.type', '=', 'nuevo-antiguo');
            })
            ->leftJoin('files', function ($join) {
                $join->on('files.model_id', '=', 'lendings.expense_id')
                     ->where('files.model_name', '=', 'expenses');
            })
            ->leftJoin('files as f', function ($join) {
                $join->on('f.model_id', '=', 'news.id')
                     ->where('f.model_name', '=', 'news')
                     ->where('f.name', '=', 'FOTO_VOUCHER');
            })
            ->leftJoin('files as filePdf', function ($join) {
                $join->on('filePdf.model_id', '=', 'news.id')
                     ->where('filePdf.model_name', '=', 'news')
                     ->where('filePdf.name', '=', 'PDF_CV');
            })
            ->whereIn('lendings.status', [$status3])
            ->where('lendings.listing_id', $idList)
            ->distinct()
            ->orderBy('lendings.type', 'asc')
            ->orderBy('lendings.id', 'asc')
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
            $items = Lending::where('id', '=', $id)
                ->with('payments')
                ->with('discounts')
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
            'data' => $items,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function store(Request $request)
    {
        try {
            $idUserSesion = $request->user()->id;
            $period = $request->period;
            $userSend = $request->user_send;
            $city = $request->city;
            $countDays = 1;
            $amountFees = 1;

            $date = date("Y-m-d");
            $firstDate = date("Y-m-d H:i:s", (strtotime(date($date))));

            if ($period === 'diario') {
                $countDays = 21;
                $amountFees = 22;
            } else if ($period === 'semanal') {
                $countDays = 21;
                $amountFees = 3;
            } else if ($period === 'quincenal') {
                $countDays = 14;
                $amountFees = 1;
            }

            $endDate = date("Y-m-d H:i:s", (strtotime(date($date)) + (86400 * $countDays) + 86399));

            $idList = 1;
            $idUserExpense = 1;

            $result = DB::select("SELECT
                                lis.id as id,
                                lis.name as name,
                                lis.user_id_collector as user_id,
                                COALESCE(SUM(len.amount), 0) AS capital
                                FROM listings lis
                                LEFT JOIN lendings as len ON lis.id = len.listing_id AND len.status = 'open'
                                WHERE lis.city_id =".$city."
                                GROUP BY lis.id
                                ORDER BY COALESCE(SUM(len.amount), 0) ASC;");

            if ($userSend) {
                $result = DB::select("SELECT
                    lis.id as id,
                    lis.name as name,
                    lis.user_id_collector as user_id,
                    COALESCE(SUM(len.amount), 0) AS capital
                    FROM listings lis
                    LEFT JOIN lendings as len ON lis.id = len.listing_id AND len.status = 'open'
                    WHERE lis.user_id_collector =".$userSend."
                    GROUP BY lis.id
                    ORDER BY COALESCE(SUM(len.amount), 0) ASC;");
            }

            if (!empty($result)) {
                $firstRow = $result[0];
                $idList = $firstRow->id;
                $idUserExpense = $firstRow->user_id;
            }

            $statusLending = Lending::create([
                'nameDebtor' => $request->nameDebtor,
                'address' => $request->address,
                'phone' => $request->phone,
                'firstDate' => $firstDate,
                'endDate' => $endDate,
                'amount' => $request->amount,
                'amountFees' => $amountFees,
                'percentage' => $request->percentage,
                'period' => $period,
                'order' => $request->order,
                'status' => $request->status,
                'listing_id' => $idList,
                'new_id' => $request->new_id,
                'type' => $request->type,
            ]);

            $itemFile = File::where('name', 'FOTO_VOUCHER')
            ->where('model_id', $request->new_id)
            ->where('model_name', 'news')
            ->first();

            $statusExpense = Expense::create([
                'date' => $firstDate,
                'amount' => $request->amount,
                'status' => 'aprobado',
                'description' => 'Egreso creado automaticamente cuando se aprueba el voucher de consignación del nuevo',
                'item_id' => 8, // id del item de egreso para NUEVOS
                'user_id' => $idUserExpense,
                'file_id' => $itemFile->id,
                'registered_by' => $idUserSesion,
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
            'message' => [
                [
                    'text' => 'Eliminado con éxito.',
                    'detail' => null,
                ]
            ]
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

    public function renovate(Request $request, $id)
    {
        try {
            $idUserSesion = $request->user()->id;
            $action = $request->action;
            $newItem = [
                'status' => $request->status,
            ];

            $item = Lending::find($id);

            $item->update($newItem);

            $period = $request->period;
            $countDays = 1;
            $amountFees = 1;

            $date = $request->date;
            $firstDate = date("Y-m-d H:i:s", (strtotime(date($date))));
            $currentDate = date("Y-m-d H:i:s");

            if ($period === 'diario') {
                $countDays = 21;
                $amountFees = 22;
            } else if ($period === 'semanal') {
                $countDays = 21;
                $amountFees = 3;
            } else if ($period === 'quincenal') {
                $countDays = 14;
                $amountFees = 1;
            }

            $endDate = date("Y-m-d H:i:s", (strtotime(date($date)) + (86400 * $countDays) + 86399));

            $idList = $item->listing_id;
            $amount = $request->amount;
            $repayment = $request->repayment;

            $idUserExpense = 1;

            $result = Listing::find($idList);

            if (!empty($result)) {
                $idList = $result->id;
                $idUserExpense = $result->user_id_collector;
            }

            $itemExpense = null;
            if ($action == 'transfer') { // SI ES TIPO TRANSFERENCIA, CREAR EXPENSE
                $itemExpense = Expense::create([
                    'date' => $currentDate,
                    'amount' => $repayment,
                    'status' => 'creado',
                    'description' => 'Egreso creado al renovar el credito, y se debe transferir dinero al cliente',
                    'item_id' => 1, // id del item de egreso para RENOVACIONES DE NEQUI
                    'user_id' => $idUserExpense,
                    'registered_by' => $idUserSesion,
                ]);
            }

            $itemLending = Lending::create([
                'nameDebtor' => $item->nameDebtor,
                'address' => $item->address,
                'phone' => $item->phone,
                'firstDate' => $firstDate,
                'endDate' => $endDate,
                'amount' => $amount,
                'amountFees' => $amountFees,
                'percentage' => $item->percentage,
                'period' => $period,
                'order' => $item->order,
                'status' => 'open',
                'expense_id' => $itemExpense ? $itemExpense->id : null,
                'listing_id' => $idList,
                'new_id' => $item->new_id,
                'type' => 'R',
            ]);

            if ($action == 'repayment') { // SI ES TIPO ADELANTO, CREAR PAYMENT
                $itemPayment = Payment::create([
                    'lending_id' => $itemLending->id,
                    'date' => $currentDate,
                    'amount' => $repayment,
                    'status' => 'aprobado',
                    'observation' => 'adelanto',
                    'type' => 'nequi',
                    'is_valid' => 1,
                    'date_transaction' => $currentDate,
                ]);
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
            'message' => [
                [
                    'text' => 'Modificado con éxito.',
                    'detail' => $item,
                ]
            ]
        ], JsonResponse::HTTP_OK);
    }

    public function renewOld(Request $request)
    {
        try {
            $idUserSesion = $request->user()->id;
            $amount = $request->amount;
            $period = $request->period;

            $itemNovel = Novel::find($request->new_id);
            $novelItem = [
                'status' => 'aprobado',
                'quantity' => $amount,
                'period' => $period,
            ];

            $itemNovel->update($novelItem);

            $itemLendingOld = Lending::find($request->lending_id);
            $dateUpdateOld = $itemLendingOld->updated_at;
            $newItem = [
                'status' => 'off',
                'updated_at' => $dateUpdateOld,
            ];

            $itemLendingOld->update($newItem);

            $countDays = 1;
            $amountFees = 1;

            $date = date("Y-m-d H:i:s");
            $firstDate = date("Y-m-d H:i:s", (strtotime(date($date))));
            $currentDate = date("Y-m-d H:i:s");

            if ($period === 'diario') {
                $countDays = 21;
                $amountFees = 22;
            } else if ($period === 'semanal') {
                $countDays = 21;
                $amountFees = 3;
            } else if ($period === 'quincenal') {
                $countDays = 14;
                $amountFees = 1;
            }

            $endDate = date("Y-m-d H:i:s", (strtotime(date($date)) + (86400 * $countDays) + 86399));

            $idList = $itemLendingOld->listing_id;

            $idUserExpense = 1;

            $result = Listing::find($idList);

            if (!empty($result)) {
                $idList = $result->id;
                $idUserExpense = $result->user_id_collector;
            }

            $itemExpense = Expense::create([
                'date' => $currentDate,
                'amount' => $amount,
                'status' => 'creado',
                'description' => 'Egreso creado al regresar un nuevo antiguo, y se debe transferir dinero al cliente',
                'item_id' => 8, // id del item de egreso para NUEVOS
                'user_id' => $idUserExpense,
                'registered_by' => $idUserSesion,
            ]);

            $itemLending = Lending::create([
                'nameDebtor' => $itemLendingOld->nameDebtor,
                'address' => $itemLendingOld->address,
                'phone' => $itemLendingOld->phone,
                'firstDate' => $firstDate,
                'endDate' => $endDate,
                'amount' => $amount,
                'amountFees' => $amountFees,
                'percentage' => $itemLendingOld->percentage,
                'period' => $period,
                'order' => 1,
                'status' => 'open',
                'expense_id' => $itemExpense ? $itemExpense->id : null,
                'listing_id' => $idList,
                'new_id' => $itemLendingOld->new_id,
                'type' => 'N',
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
                    'text' => 'Modificado con éxito.',
                    'detail' => $itemLending,
                ]
            ]
        ], JsonResponse::HTTP_OK);
    }

    public function history(Request $request, $idNew)
    {
        try {
            $idUserSesion = $request->user()->id;
            $items = Lending::where('new_id', '=', $idNew)
                                ->with('payments')
                                ->with('discounts')
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

}
