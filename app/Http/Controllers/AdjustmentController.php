<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\AdjustmentServiceImplement;

class AdjustmentController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, AdjustmentServiceImplement $service) { 
            $this->request = $request;
            $this->service = $service;
    }

    function list(){
        return $this->service->list();
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

    function createFromProccess(){
        return $this->service->createFromProccess($this->request->all());
    }
}
