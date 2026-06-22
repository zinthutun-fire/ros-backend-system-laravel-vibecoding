<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function dailySales(): JsonResponse
    {
        $date = request()->get('date', today()->toDateString());
        return response()->json($this->reportService->dailySales($date));
    }

    public function monthlySales(): JsonResponse
    {
        $year = request()->get('year', now()->year);
        $month = request()->get('month', now()->month);
        return response()->json($this->reportService->monthlySales((int) $year, (int) $month));
    }

    public function yearlySales(): JsonResponse
    {
        $year = request()->get('year', now()->year);
        return response()->json($this->reportService->yearlySales((int) $year));
    }

    public function topItems(): JsonResponse
    {
        $from = request()->get('from', now()->startOfMonth()->toDateString());
        $to = request()->get('to', now()->toDateString());
        $limit = (int) request()->get('limit', 10);
        return response()->json($this->reportService->topItems($from, $to, $limit));
    }

    public function tableUtilization(): JsonResponse
    {
        $date = request()->get('date', today()->toDateString());
        return response()->json($this->reportService->tableUtilization($date));
    }
}
