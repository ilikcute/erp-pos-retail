<?php

namespace App\Actions\Cache;

use Illuminate\Support\Facades\DB;

class PreventN1QueriesAction
{
    public function optimizeSalesTransactionLoading()
    {
        $transactions = DB::table('sales_transactions')
            ->with([
                'items.product',
                'items.product.variants',
                'payments.paymentMethod',
                'discounts',
                'cashier',
                'customer',
            ])
            ->where('status', 'POSTED')
            ->limit(100)
            ->get();

        return $transactions;
    }

    public function optimizeInventoryLedgerLoading()
    {
        $ledger = DB::table('inventory_ledgers')
            ->with([
                'product',
                'product.variants',
                'location',
            ])
            ->orderBy('reference_date', 'desc')
            ->limit(1000)
            ->get();

        return $ledger;
    }

    public function optimizePurchaseOrderLoading()
    {
        $purchaseOrders = DB::table('purchase_orders')
            ->with([
                'items.product',
                'items.product.variants',
                'supplier',
                'createdBy',
            ])
            ->where('status', '!=', 'DRAFT')
            ->get();

        return $purchaseOrders;
    }

    public function useSelectOnlyNeededColumns()
    {
        $users = DB::table('users')
            ->select('id', 'name', 'email', 'status')
            ->limit(100)
            ->get();

        return $users;
    }

    public function useWithCountInsteadOfLoadingRelations()
    {
        $customers = DB::table('customers')
            ->withCount('sales')
            ->withCount('loyalty_accounts')
            ->get();

        return $customers;
    }
}
