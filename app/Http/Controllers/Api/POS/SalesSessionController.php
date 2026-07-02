<?php

namespace App\Http\Controllers\Api\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\CloseSessionRequest;
use App\Http\Requests\POS\OpenSessionRequest;
use App\Http\Resources\POS\SalesSessionResource;
use App\Services\POS\SalesSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesSessionController extends Controller
{
    public function __construct(
        private readonly SalesSessionService $sessionService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $sessions = $this->sessionService->paginate(
            $request->only(['search', 'status', 'cashier_id', 'date_from', 'date_to']),
            $request->input('per_page', 15),
        );

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => SalesSessionResource::collection($sessions),
            'meta' => [
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
                'per_page' => $sessions->perPage(),
                'total' => $sessions->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $session = $this->sessionService->findById($id);

        if (! $session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => new SalesSessionResource($session),
        ]);
    }

    public function open(OpenSessionRequest $request): JsonResponse
    {
        $session = $this->sessionService->openSession(
            shiftId: $request->input('shift_id'),
            cashierId: auth()->id(),
            openingCash: $request->input('opening_cash'),
            notes: $request->input('notes'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Session opened successfully',
            'data' => new SalesSessionResource($session),
        ], 201);
    }

    public function close(CloseSessionRequest $request, int $id): JsonResponse
    {
        $session = $this->sessionService->findById($id);

        if (! $session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
            ], 404);
        }

        $session = $this->sessionService->closeSession(
            session: $session,
            closingCash: $request->input('closing_cash'),
            notes: $request->input('notes'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Session closed successfully',
            'data' => new SalesSessionResource($session),
        ]);
    }

    public function myOpenSession(): JsonResponse
    {
        $session = $this->sessionService->findOpenByCashier(auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => $session ? new SalesSessionResource($session) : null,
        ]);
    }
}
