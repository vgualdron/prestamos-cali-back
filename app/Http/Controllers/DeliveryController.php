<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\DeliveryServiceImplement;

class DeliveryController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, DeliveryServiceImplement $service) {
            $this->request = $request;
            $this->service = $service;
    }

    function create() {
        return $this->service->create($this->request->all());
    }
}
