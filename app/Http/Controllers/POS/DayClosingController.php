<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\CloseDayRequest;
use App\Http\Resources\POS\DayClosingResource;
use App\Models\System\User;
use App\Repositories\Contracts\POS\DayClosingRepositoryInterface;
use App\Services\POS\DayClosingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        if (! $closing) {
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

        // Jika kasir biasa (tidak memiliki role admin/supervisor/manager/owner), wajib memvalidasi kredensial supervisor
        if (! $user->hasAnyRole(['admin', 'supervisor', 'manager', 'owner']) && ! $user->hasPermission('pos.day-closing.manage')) {
            $supervisor = User::where('email', $request->supervisor_email)->first();

            if (! $supervisor || ! Hash::check($request->supervisor_password, $supervisor->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kredensial supervisor salah atau tidak ditemukan.',
                ], 422);
            }

            if (! $supervisor->hasAnyRole(['admin', 'supervisor', 'manager', 'owner']) && ! $supervisor->hasPermission('pos.day-closing.manage')) {
                return response()->json([
                    'success' => false,
                    'message' => 'User supervisor yang dimasukkan tidak memiliki wewenang tutup buku.',
                ], 403);
            }

            // Gabungkan catatan jika supervisor mengoverride
            $request->merge([
                'notes' => trim(($request->notes ?? '')."\nDiotorisasi oleh supervisor: ".$supervisor->name),
            ]);
        }

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
