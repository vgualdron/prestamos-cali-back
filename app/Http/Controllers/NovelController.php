<?php

namespace App\Http\Controllers;
use App\Models\Novel;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Implementations\NovelServiceImplement;

class NovelController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, NovelServiceImplement $service) {
        $this->request = $request;
        $this->service = $service;
    }

    function list(string $status){
        return $this->service->list($status);
    }

    function listForUpdate(string $status, string $query){
        return $this->service->listForUpdate($status, $query);
    }

    function listForLetter(string $status){
        return $this->service->listForLetter($status);
    }

    function listReds(int $city, int $user){
        return $this->service->listReds($city, $user);
    }

    function create(){
        $item = $this->request->all();
        $userSesion = $this->request->user();
        $idUserSesion = $userSesion->id;
        $item["registered_by"] = $idUserSesion;
        return $this->service->create($item);
    }

    function update(int $id){
        return $this->service->update($this->request->all(), $id);
    }

    function updateStatus(int $id){
        return $this->service->updateStatus($this->request->all(), $id);
    }

    function completeData(int $id) {

        try {
            $item = Novel::find($id)->update($this->request->all());
        } catch (Exception $e) {
            return response()->json([
                'data' => [],
                'message'=>$e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $item,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    function delete(int $id){
        return $this->service->delete($id);
    }

    function get(int $id){
        return $this->service->get($id);
    }

    function getByPhone(string $phone){
        return $this->service->getByPhone($phone);
    }
}
