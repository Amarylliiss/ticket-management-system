<?php

namespace App\Http\Controllers\Api;

use App\Application\Statistics\ResponseTimeReport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StatisticsRequest;
use Illuminate\Http\JsonResponse;

class StatisticsController extends Controller
{
    public function responseTimes(StatisticsRequest $request, ResponseTimeReport $report): JsonResponse
    {
        return response()->json($report->handle([
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'category_id' => $request->integer('category_id') ?: null,
            'priority' => $request->input('priority'),
        ]));
    }
}
