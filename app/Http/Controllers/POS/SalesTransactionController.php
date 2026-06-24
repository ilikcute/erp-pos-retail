<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\POS\SalesTransactionRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SalesTransactionController extends Controller
{
    public function __construct(
        private readonly SalesTransactionRepositoryInterface $transactionRepository
    ) {}

    public function index(Request $request): Response
    {
        $transactions = $this->transactionRepository->paginate(
            $request->only(['search', 'status']),
            $request->integer('per_page', 15)
        );

        return Inertia::render('POS/Orders/Index', [
            'transactions' => $transactions,
            'filters'      => $request->only(['search', 'status']),
        ]);
    }

    public function show(int $id): Response
    {
        $transaction = $this->transactionRepository->findById($id);

        if (!$transaction) {
            abort(404);
        }

        return Inertia::render('POS/Orders/Show', [
            'transaction' => $transaction,
        ]);
    }
}
