<?php

namespace App\Http\Controllers\Api\Loyalty;

use App\Http\Controllers\Controller;
use App\Http\Resources\Loyalty\AccountResource;
use App\Repositories\Contracts\Loyalty\AccountRepositoryInterface;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(
        private readonly AccountRepositoryInterface $accountRepo
    ) {}

    public function show(int $customerId)
    {
        $account = $this->accountRepo->findByCustomerId($customerId);

        if (! $account) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Customer belum memiliki akun loyalty',
            ]);
        }

        return new AccountResource($account);
    }

    public function transactions(int $customerId, Request $request)
    {
        $account = $this->accountRepo->findByCustomerId($customerId);

        if (! $account) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $transactions = $account->transactions()
            ->when($request->type, fn ($q) => $q->where('transaction_type', $request->type))
            ->when($request->date_from, fn ($q) => $q->whereDate('transaction_date', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('transaction_date', '<=', $request->date_to))
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }
}
