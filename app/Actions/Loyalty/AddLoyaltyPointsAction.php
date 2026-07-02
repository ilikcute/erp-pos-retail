<?php

namespace App\Actions\Loyalty;

use App\Models\Loyalty\LoyaltyAccount;
use App\Models\Loyalty\LoyaltyTransaction;
use App\Support\AuditService;
use Illuminate\Support\Facades\DB;

class AddLoyaltyPointsAction
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function execute(LoyaltyAccount $account, float $points, string $reason, ?int $transactionId = null): LoyaltyTransaction
    {
        return DB::transaction(function () use ($account, $points, $reason, $transactionId) {
            $transaction = LoyaltyTransaction::create([
                'loyalty_account_id' => $account->id,
                'transaction_date' => now(),
                'transaction_type' => $points > 0 ? 'EARN' : 'REDEEM',
                'points' => abs($points),
                'reference_type' => $transactionId ? 'SALES_TRANSACTION' : null,
                'reference_id' => $transactionId,
                'reason' => $reason,
                'created_by' => auth()->id(),
            ]);

            $newBalance = $account->current_points + $points;
            $account->update(['current_points' => max(0, $newBalance)]);

            $this->auditService->log(
                module: 'Loyalty',
                action: 'ADD_LOYALTY_POINTS',
                tableName: 'loyalty_transactions',
                recordId: $transaction->id,
                documentNo: "LTX-{$transaction->id}",
                newValues: [
                    'points' => $points,
                    'current_balance' => $account->current_points,
                ],
            );

            return $transaction;
        });
    }
}
