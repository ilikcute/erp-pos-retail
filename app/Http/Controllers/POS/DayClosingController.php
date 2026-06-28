<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\CloseDayRequest;
use App\Http\Resources\POS\DayClosingResource;
use App\Repositories\Contracts\POS\DayClosingRepositoryInterface;
use App\Services\POS\DayClosingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DayClosingController extends Controller
{
    public function __construct(
        private readonly DayClosingService $dayClosingService,
        private readonly DayClosingRepositoryInterface $dayClosingRepo,
    ) {}

    /**
     * GET /pos/day-closings
     */
    public function index(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'status', 'location_id']);
        $closings = $this->dayClosingRepo->getAll($filters);

        return DayClosingResource::collection($closings);
    }

    /**
     * GET /pos/day-closings/today
     * Statistik hari ini (real-time)
     */
    public function todayStats(Request $request)
    {
        $locationId = $request->input('location_id');
        $stats = $this->dayClosingService->getTodayStats($locationId);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * GET /pos/day-closings/{id}
     */
    public function show(int $id)
    {
        $closing = $this->dayClosingRepo->findById($id);

        if (!$closing) {
            return response()->json([
                'success' => false,
                'message' => 'Tutup harian tidak ditemukan',
            ], 404);
        }

        return new DayClosingResource($closing);
    }

    /**
     * POST /pos/day-closings/close
     */
    public function close(CloseDayRequest $request)
    {
        $user = Auth::user();

        try {
            $closing = $this->dayClosingService->closeDay(
                closingDate: $request->closing_date,
                closedBy: $user->id,
                locationId: $request->location_id,
                notes: $request->notes
            );

            return (new DayClosingResource($closing))
                ->additional([
                    'success' => true,
                    'message' => 'Tutup harian berhasil dieksekusi',
                ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /pos/day-closings/check
     * Cek apakah boleh transaksi pada tanggal tertentu
     */
    public function check(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $locationId = $request->input('location_id');

        $result = $this->dayClosingService->canTransactOnDate($date, $locationId);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
