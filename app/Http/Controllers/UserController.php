<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\UserServiceImplement;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\JsonResponse;

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

    public function getInfo(Request $request)
    {
        $data = null;
        try {
            $idUserSesion = $request->user()->id;
            $date = date("Y-m-d");
            $currentDate = date('Y-m-d H:i:s');

            $sqlAmountAddress = "
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
                                    AND (
                                        (DAY('" . $currentDate . "') <= 14 AND DAY(news.created_at) BETWEEN 1 AND 14)
                                        OR
                                        (DAY('" . $currentDate . "') > 14 AND DAY(news.created_at) BETWEEN 15 AND 31)
                                    )
                                WHERE users.id = " . $idUserSesion . "
                                GROUP BY users.id, users.name
                                ORDER BY total DESC;";

            // echo $sqlAmountAddress;

            $amountAddress = DB::selectOne($sqlAmountAddress);


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
                    END AS total
                FROM
                    deliveries
                WHERE
                    MONTH(date) = MONTH('" . $currentDate . "')
                    AND YEAR(date) = YEAR('" . $currentDate . "')
                    AND date <= '" . $currentDate . "'
            ");


            $data = [
                'price' => 2000,
                'amountDiary' => 3,
                'amountAddress' => $amountAddress->total,
                'days' => $days->total,
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
