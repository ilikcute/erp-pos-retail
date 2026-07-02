<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Repositories\Contracts\Accounting\CoaRepositoryInterface;
use Illuminate\Http\Request;

class CoaController extends Controller
{
    public function __construct(
        private readonly CoaRepositoryInterface $coaRepo
    ) {}

    public function index(Request $request)
    {
        $asTree = $request->boolean('tree', false);

        if ($asTree) {
            return response()->json([
                'success' => true,
                'data' => $this->coaRepo->getTree(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->coaRepo->getAll($request->only(['account_type', 'is_active'])),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|integer|exists:chart_of_accounts,id',
            'account_code' => 'required|string|unique:chart_of_accounts,account_code',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
            'normal_balance' => 'required|in:DEBIT,CREDIT',
            'is_postable' => 'boolean',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $account = ChartOfAccount::create(array_merge($validated, [
            'created_by' => auth()->id(),
        ]));

        return response()->json([
            'success' => true,
            'data' => $account,
            'message' => 'Akun berhasil dibuat',
        ], 201);
    }

    public function show(int $id)
    {
        $account = ChartOfAccount::with(['parent', 'children'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $account]);
    }

    public function update(Request $request, int $id)
    {
        $account = ChartOfAccount::findOrFail($id);

        $validated = $request->validate([
            'parent_id' => 'nullable|integer|exists:chart_of_accounts,id',
            'account_code' => 'string|unique:chart_of_accounts,account_code,'.$id,
            'account_name' => 'string|max:255',
            'account_type' => 'in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
            'normal_balance' => 'in:DEBIT,CREDIT',
            'is_postable' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $account->update($validated);

        return response()->json([
            'success' => true,
            'data' => $account->fresh(),
            'message' => 'Akun berhasil diupdate',
        ]);
    }

    public function destroy(int $id)
    {
        $account = ChartOfAccount::findOrFail($id);

        // Cek apakah punya child
        if ($account->children()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa hapus akun yang punya child',
            ], 422);
        }

        // Cek apakah sudah ada jurnal
        if ($account->journalLines()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa hapus akun yang sudah digunakan',
            ], 422);
        }

        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dihapus',
        ]);
    }
}
