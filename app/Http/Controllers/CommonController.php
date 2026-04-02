<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReceivableService;

class CommonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');        
    }

    public function getReceivables(int $customerId, ReceivableService $receivableService): array
    {
        return $receivableService->getReceivables($customerId);
    }
}
