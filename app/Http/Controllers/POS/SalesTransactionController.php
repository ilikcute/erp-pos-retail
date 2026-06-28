<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\POS\SalesTransactionRepositoryInterface;
use App\Repositories\Contracts\POS\ShiftRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SalesTransactionController extends Controller
{
    public function __construct(
        private readonly SalesTransactionRepositoryInterface $transactionRepository,
        private readonly ShiftRepositoryInterface $shiftRepository
    ) {}

    public function shifts(Request $request): Response
    {
        $shifts = $this->shiftRepository->paginate($request->only('search'), 50);

        return Inertia::render('POS/Shifts/Index', [
            'shifts' => $shifts,
            'filters' => $request->only('search'),
        ]);
    }

    public function index(Request $request): Response
    {
        $transactions = $this->transactionRepository->paginate(
            $request->only(['search', 'status']),
            $request->integer('per_page', 15)
        );

        return Inertia::render('POS/Orders/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(int $id): Response
    {
        $transaction = $this->transactionRepository->findById($id);

        if (! $transaction) {
            abort(404);
        }

        return Inertia::render('POS/Orders/Show', [
            'transaction' => $transaction,
        ]);
    }

    public function shiftsList(Request $request): \Illuminate\Http\JsonResponse
    {
        $shifts = $this->shiftRepository->findActive();

        $data = $shifts->map(function ($shift) {
            return [
                'id'          => $shift->id,
                'shift_code'  => $shift->shift_code,
                'shift_name'  => $shift->shift_name,
                'name'        => $shift->shift_name, // fallback for ShiftOpener.vue
                'start_time'  => $shift->start_time?->format('H:i') ?? $shift->start_time,
                'end_time'    => $shift->end_time?->format('H:i') ?? $shift->end_time,
                'is_active'   => $shift->is_active,
                'description' => $shift->description,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
