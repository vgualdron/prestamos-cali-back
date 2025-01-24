<?php

namespace App\Http\Controllers;
use App\Models\Reddirection;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Implementations\ReddirectionServiceImplement;

class ReddirectionController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, ReddirectionServiceImplement $service) {
        $this->request = $request;
        $this->service = $service;
    }

    function create(){
        $userSesion = $this->request->user();
        $idUserSesion = $userSesion->id;
        $data = $this->request->all();
        $data['idUserSesion'] = $idUserSesion;
        return $this->service->create($data);
    }

    function getCurrentByUser(int $user){
        return $this->service->getCurrentByUser($user);
    }

    function update(int $id){
        return $this->service->update($this->request->all(), $id);
    }

    function getByLending(int $lending){
        return $this->service->getByLending($lending);
    }

    function delete(int $id){
        return $this->service->delete($id);
    }
}
