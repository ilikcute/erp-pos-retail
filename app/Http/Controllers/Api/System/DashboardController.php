<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use App\Actions\Reporting\GenerateDashboardKPIsAction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, GenerateDashboardKPIsAction $action)
    {
        return response()->json([
            'success' => true,
            'data' => $action->execute($request->all())
        ]);
    }
}
