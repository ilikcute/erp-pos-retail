<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\CloseMonthRequest;
use App\Http\Resources\POS\MonthClosingResource;
use App\Repositories\Contracts\POS\MonthClosingRepositoryInterface;
use App\Services\POS\MonthClosingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonthClosingController extends Controller
{
    public function __construct(
        private readonly MonthClosingService $monthClosingService,
        private readonly MonthClosingRepositoryInterface $monthClosingRepo,
    ) {}

    /**
     * GET /pos/month-closings
     */
    public function index(Request $request)
    {
        $filters = $request->only(['year', 'status', 'location_id']);
        $closings = $this->monthClosingRepo->getAll($filters);

        return MonthClosingResource::collection($closings);
    }

    /**
     * GET /pos/month-closings/{year}/{month}
     */
    public function show(int $year, int $month, Request $request)
    {
        $locationId = $request->input('location_id');
        $status = $this->monthClosingService->getPeriodStatus($year, $month, $locationId);

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * POST /pos/month-closings/close
     */
    public function close(CloseMonthRequest $request)
    {
        $user = Auth::user();

        try {
            $closing = $this->monthClosingService->closeMonth(
                year: $request->closing_year,
                month: $request->closing_month,
                closedBy: $user->id,
                locationId: $request->location_id,
                notes: $request->notes
            );

            return (new MonthClosingResource($closing))
                ->additional([
                    'success' => true,
                    'message' => 'Tutup bulanan berhasil dieksekusi',
                ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /pos/month-closings/check
     * Cek apakah boleh transaksi pada periode tertentu
     */
    public function check(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $locationId = $request->input('location_id');

        $result = $this->monthClosingService->canTransactInPeriod($year, $month, $locationId);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
