<?php

namespace App\Http\Controllers\POS;

use App\Actions\POS\CloseSalesSessionAction;
use App\Actions\POS\OpenSalesSessionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\POS\CloseSessionRequest;
use App\Http\Requests\POS\OpenSessionRequest;
use App\Http\Resources\POS\CashierSessionResource;
use App\Repositories\Contracts\POS\SessionRepositoryInterface;
use App\Services\POS\SessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierSessionController extends Controller
{
    public function __construct(
        private readonly SessionService $sessionService,
        private readonly SessionRepositoryInterface $sessionRepo,
        private readonly OpenSalesSessionAction $openAction,
        private readonly CloseSalesSessionAction $closeAction,
    ) {}

    /**
     * GET /pos/sessions/active
     * Ambil sesi aktif user saat ini
     */
    public function active()
    {
        $user = Auth::user();
        $session = $this->sessionService->getActiveSession($user->id);

        if (! $session) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Tidak ada sesi aktif',
            ]);
        }

        // Refresh stats sebelum return
        $this->sessionService->refreshSessionStats($session->id);
        $session->refresh();

        return new CashierSessionResource($session);
    }

    /**
     * POST /pos/sessions/open
     * Buka sesi kasir
     */
    public function open(OpenSessionRequest $request)
    {
        $user = Auth::user();
        $userId = $request->input('user_id', $user->id);

        try {
            $session = $this->openAction->execute(
                userId: $userId,
                shiftId: $request->shift_id,
                data: [
                    'opening_cash' => (float) $request->opening_cash,
                    'location_id' => $request->location_id,
                    'notes' => $request->notes,
                ],
            );

            // Simpan session_id di session Laravel untuk POS
            session(['pos_session_id' => $session->id]);
            session(['pos_location_id' => $session->location_id]);

            return (new CashierSessionResource($session))
                ->additional([
                    'success' => true,
                    'message' => 'Sesi kasir berhasil dibuka',
                ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /pos/sessions/{id}/close
     * Tutup sesi kasir
     */
    public function close(CloseSessionRequest $request, int $id)
    {
        $session = $this->sessionRepo->findById($id);

        if (! $session) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak ditemukan',
            ], 404);
        }

        $this->authorize('close', $session);

        try {
            $session = $this->closeAction->execute(
                session: $session,
                data: [
                    'closing_cash' => (float) $request->closing_cash,
                    'reimbursement_amount' => (float) $request->input('reimbursement_amount', 0),
                    'variance_reason' => $request->input('variance_reason'),
                    'notes' => $request->notes,
                ],
            );

            // Clear session
            session()->forget(['pos_session_id', 'pos_location_id']);

            return (new CashierSessionResource($session))
                ->additional([
                    'success' => true,
                    'message' => 'Sesi kasir berhasil ditutup',
                ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /pos/sessions
     * Riwayat sesi
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'shift_id',
            'user_id',
            'status',
            'date_from',
            'date_to',
        ]);

        $sessions = $this->sessionRepo->getAll($filters);

        return CashierSessionResource::collection($sessions);
    }

    /**
     * GET /pos/sessions/{id}
     * Detail sesi
     */
    public function show(int $id)
    {
        $session = $this->sessionRepo->findById($id);

        if (! $session) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak ditemukan',
            ], 404);
        }

        return new CashierSessionResource($session);
    }
}
