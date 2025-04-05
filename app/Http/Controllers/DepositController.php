<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\DepositServiceImplement;

class DepositController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, DepositServiceImplement $service) {
            $this->request = $request;
            $this->service = $service;
    }

    function list(string $status){
        return $this->service->list($status);
    }

    function create(){
        $item = $this->request->all();
        $userSesion = $this->request->user();
        $idUserSesion = $userSesion->id;
        $item["registered_by"] = $idUserSesion;
        return $this->service->create($item);
    }

    function update(int $id){
        $item = $this->request->all();
        $userSesion = $this->request->user();
        $idUserSesion = $userSesion->id;
        $item["registered_by"] = $idUserSesion;
        return $this->service->update($item, $id);
    }

    function delete(int $id){
        return $this->service->delete($id);
    }

}
