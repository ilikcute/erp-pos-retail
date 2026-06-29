<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\POS\SalesTransactionRepositoryInterface;
use App\Repositories\Contracts\POS\ShiftRepositoryInterface;
use App\Repositories\Contracts\POS\DayClosingRepositoryInterface;
use App\Repositories\Contracts\POS\MonthClosingRepositoryInterface;
use App\Services\POS\ShiftService;
use App\Http\Requests\POS\StoreShiftRequest;
use App\Http\Requests\POS\UpdateShiftRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SalesTransactionController extends Controller
{
    public function __construct(
        private readonly SalesTransactionRepositoryInterface $transactionRepository,
        private readonly ShiftRepositoryInterface $shiftRepository,
        private readonly ShiftService $shiftService,
        private readonly DayClosingRepositoryInterface $dayClosingRepo,
        private readonly MonthClosingRepositoryInterface $monthClosingRepo,
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
        $user = \Illuminate\Support\Facades\Auth::user();
        $filters = $request->only(['search', 'status']);

        // Kasir biasa hanya boleh melihat transaksi dari lokasinya sendiri
        if (!$user->hasAnyRole(['admin', 'supervisor'])) {
            $filters['location_id'] = session('pos_location_id');
        }

        $transactions = $this->transactionRepository->paginate(
            $filters,
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

        $this->authorize('view', $transaction);

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

    public function storeShift(StoreShiftRequest $request): RedirectResponse
    {
        $this->shiftService->create($request->validated());
        return redirect()->route('pos.shifts.index')->with('success', 'Shift berhasil dibuat.');
    }

    public function updateShift(UpdateShiftRequest $request, int $id): RedirectResponse
    {
        $shift = $this->shiftService->findById($id);
        if (!$shift) {
            abort(404);
        }
        $this->shiftService->update($shift, $request->validated());
        return redirect()->route('pos.shifts.index')->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroyShift(int $id): RedirectResponse
    {
        $shift = $this->shiftService->findById($id);
        if (!$shift) {
            abort(404);
        }
        $this->shiftService->delete($shift);
        return redirect()->route('pos.shifts.index')->with('success', 'Shift berhasil dihapus.');
    }

    public function dayClosingView(Request $request): Response
    {
        $filters = $request->only(['date_from', 'date_to', 'status', 'location_id']);
        $dayClosings = $this->dayClosingRepo->getAll($filters);

        return Inertia::render('POS/DayClosing', [
            'dayClosings' => $dayClosings,
            'filters' => $filters,
        ]);
    }

    public function monthClosingView(Request $request): Response
    {
        $filters = $request->only(['year', 'status', 'location_id']);
        $monthClosings = $this->monthClosingRepo->getAll($filters);

        return Inertia::render('POS/MonthClosing', [
            'monthClosings' => $monthClosings,
            'filters' => $filters,
        ]);
    }
}
