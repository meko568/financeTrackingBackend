<?php

namespace App\Http\Controllers;

use App\Services\BudgetAlertService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BudgetAlertController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();
        $alerts = app(BudgetAlertService::class)->checkAlerts($user->id);

        return $this->success(['alerts' => $alerts]);
    }
}
