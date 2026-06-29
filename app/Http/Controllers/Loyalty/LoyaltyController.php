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
use App\Models\Product\Product;
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
        $rewards = LoyaltyRewardCatalog::all(); // load all rewards for catalog management
        $customers = Customer::whereDoesntHave('loyaltyAccount')->get();
        $loyaltyRedemptions = LoyaltyRedemption::with(['account.customer', 'reward'])->orderBy('created_at', 'desc')->get();
        $loyaltyConfiguration = LoyaltyConfiguration::first();
        $products = Product::orderBy('product_name')->get();

        return Inertia::render('Loyalty/Index', [
            'loyaltyAccounts' => $loyaltyAccounts,
            'loyaltyTransactions' => $loyaltyTransactions,
            'membershipTiers' => $membershipTiers,
            'rewards' => $rewards,
            'customers' => $customers,
            'loyaltyRedemptions' => $loyaltyRedemptions,
            'loyaltyConfiguration' => $loyaltyConfiguration,
            'products' => $products,
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

            if ($account->current_points < $reward->point_required) {
                throw new \Exception('Poin tidak mencukupi untuk menukarkan hadiah ini.');
            }

            // Decrement points immediately as reserved
            $account->decrement('current_points', $reward->point_required);

            $latest = LoyaltyRedemption::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $redemptionNo = 'LRD-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            LoyaltyRedemption::create([
                'redemption_number' => $redemptionNo,
                'loyalty_account_id' => $account->id,
                'reward_catalog_id' => $reward->id,
                'points_used' => $reward->point_required,
                'status' => \App\Enums\Loyalty\RedemptionStatus::PENDING,
            ]);

            LoyaltyTransaction::create([
                'loyalty_account_id' => $account->id,
                'points' => $reward->point_required,
                'transaction_type' => 'REDEEM',
                'reason' => 'Redeem Reward (Pending Approval): ' . $reward->reward_name,
                'transaction_date' => now()->toDateString(),
            ]);
        });

        return back()->with('success', 'Pengajuan penukaran hadiah berhasil diajukan dan sedang menunggu persetujuan.');
    }

    public function approveRedemption(int $id): RedirectResponse
    {
        $redemption = LoyaltyRedemption::findOrFail($id);
        if ($redemption->status !== \App\Enums\Loyalty\RedemptionStatus::PENDING) {
            return back()->with('error', 'Hanya pengajuan PENDING yang dapat disetujui.');
        }

        DB::transaction(function () use ($redemption) {
            $redemption->update([
                'status' => \App\Enums\Loyalty\RedemptionStatus::APPROVED,
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
            ]);

            // Increment redeemed quantity on catalog
            $reward = $redemption->reward;
            if ($reward) {
                $reward->increment('redeemed_qty', 1);
            }
        });

        return back()->with('success', 'Penukaran poin berhasil disetujui.');
    }

    public function rejectRedemption(Request $request, int $id): RedirectResponse
    {
        $request->validate(['rejection_notes' => 'required|string|max:255']);
        $redemption = LoyaltyRedemption::findOrFail($id);
        if ($redemption->status !== \App\Enums\Loyalty\RedemptionStatus::PENDING) {
            return back()->with('error', 'Hanya pengajuan PENDING yang dapat ditolak.');
        }
        
        DB::transaction(function() use ($redemption, $request) {
            $redemption->update([
                'status' => \App\Enums\Loyalty\RedemptionStatus::REJECTED,
                'rejection_notes' => $request->rejection_notes,
            ]);
            // Refund points
            $account = $redemption->account;
            $account->increment('current_points', $redemption->points_used);
            // Record refund transaction
            LoyaltyTransaction::create([
                'loyalty_account_id' => $account->id,
                'points' => $redemption->points_used,
                'transaction_type' => 'EARN',
                'reason' => 'Pengembalian poin (Redemption ditolak): ' . ($redemption->reward->reward_name ?? 'Reward'),
                'transaction_date' => now()->toDateString(),
            ]);
        });

        return back()->with('success', 'Penukaran poin berhasil ditolak dan poin dikembalikan.');
    }

    public function updateConfiguration(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'point_expiry_months' => 'required|integer|min:1',
            'minimum_redeem_points' => 'required|integer|min:0',
            'point_value' => 'required|integer|min:1',
            'earn_rate' => 'required|numeric|min:1',
            'allow_negative_point' => 'boolean',
            'is_enabled' => 'boolean',
            'terms_and_conditions' => 'nullable|string',
        ]);

        $config = LoyaltyConfiguration::firstOrCreate([]);
        $config->update($validated);

        return back()->with('success', 'Konfigurasi loyalty berhasil diperbarui.');
    }

    public function storeReward(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reward_code' => 'required|string|max:50|unique:loyalty_reward_catalogs,reward_code',
            'reward_name' => 'required|string|max:255',
            'reward_type' => 'required|in:VOUCHER,PRODUCT,LUCKY_DRAW',
            'point_required' => 'required|integer|min:1',
            'voucher_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'product_id' => 'nullable|exists:products,id',
            'stock_qty' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        LoyaltyRewardCatalog::create($validated);

        return back()->with('success', 'Reward Catalog berhasil ditambahkan.');
    }

    public function updateReward(Request $request, int $id): RedirectResponse
    {
        $reward = LoyaltyRewardCatalog::findOrFail($id);
        $validated = $request->validate([
            'reward_code' => 'required|string|max:50|unique:loyalty_reward_catalogs,reward_code,' . $id,
            'reward_name' => 'required|string|max:255',
            'reward_type' => 'required|in:VOUCHER,PRODUCT,LUCKY_DRAW',
            'point_required' => 'required|integer|min:1',
            'voucher_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'product_id' => 'nullable|exists:products,id',
            'stock_qty' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $reward->update($validated);

        return back()->with('success', 'Reward Catalog berhasil diperbarui.');
    }

    public function destroyReward(int $id): RedirectResponse
    {
        $reward = LoyaltyRewardCatalog::findOrFail($id);
        $reward->delete();
        return back()->with('success', 'Reward Catalog berhasil dihapus.');
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

    public function updateTier(Request $request, int $id): RedirectResponse
    {
        $tier = LoyaltyTier::findOrFail($id);
        $validated = $request->validate([
            'tier_name' => 'required|string|max:50|unique:loyalty_tiers,tier_name,' . $id,
            'minimum_points' => 'required|integer|min:0',
            'point_multiplier' => 'required|numeric|min:1',
            'discount_percentage' => 'required|numeric|min:0|max:100',
        ]);
        $tier->update($validated);
        return back()->with('success', 'Membership Tier berhasil diperbarui.');
    }

    public function destroyTier(int $id): RedirectResponse
    {
        $tier = LoyaltyTier::findOrFail($id);
        $tier->delete();
        return back()->with('success', 'Membership Tier berhasil dihapus.');
    }
}
