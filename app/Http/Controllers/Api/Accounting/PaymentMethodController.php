<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\PaymentMethod;
use App\Repositories\Contracts\Accounting\PaymentMethodRepositoryInterface;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function __construct(
        private readonly PaymentMethodRepositoryInterface $pmRepo
    ) {}

    public function index(Request $request)
    {
        $methods = $this->pmRepo->getAll($request->only(['method_type', 'is_active']));

        $data = $methods->map(function ($m) {
            return [
                'id' => $m->id,
                'method_code' => $m->method_code,
                'method_name' => $m->method_name,
                'method_type' => $m->method_type->value,
                'is_cash' => $m->isCash(),
                'account_id' => $m->account_id,
                'account_name' => $m->account?->account_name,
                'is_active' => $m->is_active,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'method_code' => 'required|string|unique:payment_methods,method_code',
            'method_name' => 'required|string|max:255',
            'method_type' => 'required|in:CASH,QRIS,DEBIT,CREDIT_CARD,TRANSFER,LOYALTY_POINT,OTHER',
            'account_id' => 'required|integer|exists:chart_of_accounts,id',
            'gateway_code' => 'nullable|string',
            'logo_url' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Validasi: account harus postable
        $account = \App\Models\Accounting\ChartOfAccount::findOrFail($validated['account_id']);
        if (!$account->is_postable) {
            return response()->json([
                'success' => false,
                'message' => 'Akun harus bertipe postable (bisa ditransaksikan)',
            ], 422);
        }

        $method = PaymentMethod::create(array_merge($validated, [
            'created_by' => auth()->id(),
        ]));

        return response()->json([
            'success' => true,
            'data' => $method->load('account'),
            'message' => 'Metode pembayaran berhasil dibuat',
        ], 201);
    }

    public function show(int $id)
    {
        $method = PaymentMethod::with('account')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $method]);
    }

    public function update(Request $request, int $id)
    {
        $method = PaymentMethod::findOrFail($id);

        $validated = $request->validate([
            'method_code' => 'string|unique:payment_methods,method_code,' . $id,
            'method_name' => 'string|max:255',
            'method_type' => 'in:CASH,QRIS,DEBIT,CREDIT_CARD,TRANSFER,LOYALTY_POINT,OTHER',
            'account_id' => 'integer|exists:chart_of_accounts,id',
            'is_active' => 'boolean',
        ]);

        $method->update($validated);

        return response()->json([
            'success' => true,
            'data' => $method->fresh()->load('account'),
            'message' => 'Metode pembayaran berhasil diupdate',
        ]);
    }

    public function destroy(int $id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->delete(); // soft delete = nonaktifkan

        return response()->json([
            'success' => true,
            'message' => 'Metode pembayaran dinonaktifkan',
        ]);
    }
}
