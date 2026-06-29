<?php

namespace App\Http\Controllers\Loyalty;

use App\Http\Controllers\Controller;
use App\Models\Loyalty\LoyaltyAccount;
use App\Models\Loyalty\LoyaltyTransaction;
use App\Models\Loyalty\LoyaltyTier;
use App\Models\Loyalty\LoyaltyConfiguration;
use App\Models\Loyalty\LoyaltyRewardCatalog;
use App\Models\Loyalty\LoyaltyRedemption;
use App\Models\Loyalty\LoyaltyAdjustment;
use App\Models\MasterData\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class LoyaltyController extends Controller
{
    public function index(Request $request): Response
    {
        $loyaltyAccounts = LoyaltyAccount::with(['customer', 'tier'])->get();
        $loyaltyTransactions = LoyaltyTransaction::with('loyaltyAccount.customer')
            ->orderBy('created_at', 'desc')
            ->get();
        $membershipTiers = LoyaltyTier::orderBy('minimum_points')->get();
        $rewards = LoyaltyRewardCatalog::where('is_active', true)->get();
        $customers = Customer::whereDoesntHave('loyaltyAccount')->get();

        return Inertia::render('Loyalty/Index', [
            'loyaltyAccounts' => $loyaltyAccounts,
            'loyaltyTransactions' => $loyaltyTransactions,
            'membershipTiers' => $membershipTiers,
            'rewards' => $rewards,
            'customers' => $customers,
        ]);
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id|unique:loyalty_accounts,customer_id',
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        $accountNo = 'LY-' . str_pad($customer->id, 8, '0', STR_PAD_LEFT);

        // Get regular tier
        $regularTier = LoyaltyTier::where('minimum_points', 0)->first();

        LoyaltyAccount::create([
            'customer_id' => $customer->id,
            'tier_id' => $regularTier ? $regularTier->id : null,
            'account_number' => $accountNo,
            'current_points' => 0,
            'is_active' => true,
        ]);

        return back()->with('success', 'Akun Loyalty berhasil didaftarkan.');
    }

    public function storeAdjustment(Request $request): RedirectResponse
    {
        $request->validate([
            'loyalty_account_id' => 'required|exists:loyalty_accounts,id',
            'points' => 'required|integer',
            'adjustment_type' => 'required|in:ADD,SUBTRACT',
            'remarks' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $account = LoyaltyAccount::findOrFail($request->loyalty_account_id);
            $pointsChange = $request->points;
            $type = $request->adjustment_type === 'ADD' ? 'EARN' : 'REDEEM';

            if ($request->adjustment_type === 'SUBTRACT') {
                if ($account->current_points < $pointsChange) {
                    throw new \Exception('Poin tidak mencukupi untuk melakukan pengurangan.');
                }
                $account->decrement('current_points', $pointsChange);
            } else {
                $account->increment('current_points', $pointsChange);
            }

            // Record transaction
            LoyaltyTransaction::create([
                'loyalty_account_id' => $account->id,
                'points' => $pointsChange,
                'transaction_type' => $type,
                'reason' => $request->remarks,
                'transaction_date' => now()->toDateString(),
            ]);
        });

        return back()->with('success', 'Penyesuaian poin berhasil disimpan.');
    }

    public function storeRedemption(Request $request): RedirectResponse
    {
        $request->validate([
            'loyalty_account_id' => 'required|exists:loyalty_accounts,id',
            'reward_id' => 'required|exists:loyalty_reward_catalogs,id',
        ]);

        DB::transaction(function () use ($request) {
            $account = LoyaltyAccount::findOrFail($request->loyalty_account_id);
            $reward = LoyaltyRewardCatalog::findOrFail($request->reward_id);

            if ($account->current_points < $reward->points_required) {
                throw new \Exception('Poin tidak mencukupi untuk menukarkan hadiah ini.');
            }

            $account->decrement('current_points', $reward->points_required);

            LoyaltyRedemption::create([
                'loyalty_account_id' => $account->id,
                'reward_id' => $reward->id,
                'points_redeemed' => $reward->points_required,
                'redemption_date' => now()->toDateString(),
                'status' => 'COMPLETED',
            ]);

            LoyaltyTransaction::create([
                'loyalty_account_id' => $account->id,
                'points' => $reward->points_required,
                'transaction_type' => 'REDEEM',
                'reason' => 'Redeem Reward: ' . $reward->reward_name,
                'transaction_date' => now()->toDateString(),
            ]);
        });

        return back()->with('success', 'Penukaran hadiah berhasil diproses.');
    }

    public function storeTier(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tier_name' => 'required|string|max:50|unique:loyalty_tiers,tier_name',
            'minimum_points' => 'required|integer|min:0',
            'point_multiplier' => 'required|numeric|min:1',
            'discount_percentage' => 'required|numeric|min:0|max:100',
        ]);

        LoyaltyTier::create($validated);

        return back()->with('success', 'Membership Tier berhasil dibuat.');
    }
}
