<?php

namespace App\Http\Controllers\Api\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\StoreSalesTransactionRequest;
use App\Http\Requests\POS\VoidSalesTransactionRequest;
use App\Http\Resources\POS\SalesTransactionResource;
use App\Services\POS\SalesTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesTransactionController extends Controller
{
    public function __construct(
        private readonly SalesTransactionService $transactionService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $transactions = $this->transactionService->paginate(
            $request->only(['search', 'status', 'cashier_id', 'customer_id', 'date_from', 'date_to']),
            $request->input('per_page', 15),
        );

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data'    => SalesTransactionResource::collection($transactions),
            'meta'    => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'per_page'     => $transactions->perPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $transaction = $this->transactionService->findById($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data'    => new SalesTransactionResource($transaction),
        ]);
    }

    public function store(StoreSalesTransactionRequest $request): JsonResponse
    {
        $transaction = $this->transactionService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully',
            'data'    => new SalesTransactionResource($transaction),
        ], 201);
    }

    public function void(VoidSalesTransactionRequest $request, int $id): JsonResponse
    {
        $transaction = $this->transactionService->findById($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        $transaction = $this->transactionService->voidTransaction(
            $transaction,
            $request->input('reason'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Transaction voided successfully',
            'data'    => new SalesTransactionResource($transaction),
        ]);
    }

    public function bySession(int $sessionId, Request $request): JsonResponse
    {
        $transactions = $this->transactionService->findBySession(
            $sessionId,
            $request->input('per_page', 15),
        );

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data'    => SalesTransactionResource::collection($transactions),
            'meta'    => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'per_page'     => $transactions->perPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }
}
