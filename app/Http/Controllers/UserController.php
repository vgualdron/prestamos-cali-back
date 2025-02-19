<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Lending;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Delivery;
use Illuminate\Http\Request;
use App\Services\Implementations\UserServiceImplement;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, UserServiceImplement $service) {
            $this->request = $request;
            $this->service = $service;
    }

    function list(int $displayAll){
        return $this->service->list($displayAll);
    }

    function listByRoleName(int $displayAll, string $name, int $city){
        return $this->service->listByRoleName($displayAll, $name, $city);
    }

    function listByArea(int $area){
        return $this->service->listByArea($area);
    }

    function create(){
        return $this->service->create($this->request->all());
    }

    function update(int $id){
        return $this->service->update($this->request->all(), $id);
    }

    function delete(int $id){
        return $this->service->delete($id);
    }

    function get(int $id){
        return $this->service->get($id);
    }

    function updateProfile(int $id){
        return $this->service->updateProfile($this->request->all(), $id);
    }

    function updatePushToken() {
        $user = $this->request->all();
        $userSesion = $this->request->user();
        $idUserSesion = $userSesion->id;
        return $this->service->updatePushToken($user['pushToken'], $idUserSesion);
    }

    function updateLocation() {
        $user = $this->request->all();
        $userSesion = $this->request->user();
        $idUserSesion = $userSesion->id;
        return $this->service->updateLocation($user, $idUserSesion);
    }

    public function getInfo(Request $request, $idList)
    {
        $data = null;
        try {
            $idUserSesion = $request->user()->id;
            $date = date("Y-m-d");
            $currentDate = date('Y-m-d H:i:s');

            $amountAddress = DB::selectOne("
                            SELECT
                                COUNT(news.id) AS total
                            FROM
                                users
                            LEFT JOIN
                                news ON users.id = news.user_send
                                AND MONTH(news.created_at) = MONTH('" . $currentDate . "')
                                AND YEAR(news.created_at) = YEAR('" . $currentDate . "')
                                AND news.status != 'analizando'
                                AND news.status != 'borrador'
                            WHERE users.id = " . $idUserSesion . "
                            GROUP BY
                                users.id, users.name
                            ORDER BY
                                total DESC;
                        ");

            $days = DB::selectOne("
                SELECT
                    COUNT(DISTINCT DATE(date)) +
                    CASE
                        WHEN NOT EXISTS (
                            SELECT 1
                            FROM deliveries
                            WHERE DATE(date) = DATE('" . $currentDate . "')
                        ) THEN 1
                        ELSE 0
                    END AS days_work
                FROM
                    deliveries
                WHERE
                    MONTH(date) = MONTH('" . $currentDate . "')
                    AND YEAR(date) = YEAR('" . $currentDate . "')
                    AND date <= '" . $currentDate . "'
            ");

            $data = [
                'amountAddress' => $amountAddress,
                'days' => $days,
                'date' => $currentDate,
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
