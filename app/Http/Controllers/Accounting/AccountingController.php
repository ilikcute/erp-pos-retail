<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalEntryLine;
use App\Models\Accounting\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class AccountingController extends Controller
{
    public function index(Request $request): Response
    {
        $chartOfAccounts = ChartOfAccount::orderBy('account_code')->get();
        
        $journalEntries = JournalEntry::with('lines.account')
            ->orderBy('journal_date', 'desc')
            ->get()
            ->map(function ($entry) {
                $totalDebit = $entry->lines->sum('debit');
                $totalCredit = $entry->lines->sum('credit');
                
                return array_merge($entry->toArray(), [
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                ]);
            });

        $paymentMethods = PaymentMethod::all();

        $routeName = $request->route() ? $request->route()->getName() : null;
        $activeTab = $request->query('activeTab');
        if (!$activeTab) {
            if ($routeName === 'accounting.journals') {
                $activeTab = 'journals';
            } else {
                $activeTab = 'coa';
            }
        }

        return Inertia::render('Accounting/Index', [
            'chartOfAccounts' => $chartOfAccounts,
            'journalEntries' => $journalEntries,
            'paymentMethods' => $paymentMethods,
            'activeTab' => $activeTab,
        ]);
    }

    public function storeCoa(Request $request): RedirectResponse
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

        ChartOfAccount::create(array_merge($validated, [
            'created_by' => auth()->id() ?? 1,
        ]));

        return back()->with('success', 'Akun berhasil dibuat.');
    }

    public function updateCoa(Request $request, int $id): RedirectResponse
    {
        $account = ChartOfAccount::findOrFail($id);

        $validated = $request->validate([
            'parent_id' => 'nullable|integer|exists:chart_of_accounts,id',
            'account_code' => 'string|unique:chart_of_accounts,account_code,' . $id,
            'account_name' => 'string|max:255',
            'account_type' => 'in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
            'normal_balance' => 'in:DEBIT,CREDIT',
            'is_postable' => 'boolean',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $account->update($validated);

        return back()->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroyCoa(int $id): RedirectResponse
    {
        $account = ChartOfAccount::findOrFail($id);

        if ($account->children()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus akun yang memiliki sub-akun.');
        }

        if ($account->journalLines()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus akun yang memiliki riwayat transaksi jurnal.');
        }

        $account->delete();

        return back()->with('success', 'Akun berhasil dihapus.');
    }

    public function storeJournal(Request $request): RedirectResponse
    {
        $request->validate([
            'journal_date' => 'required|date',
            'description' => 'required|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
            'lines.*.description' => 'nullable|string',
        ]);

        // Check balance
        $totalDebit = collect($request->lines)->sum('debit');
        $totalCredit = collect($request->lines)->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->with('error', 'Total Debit dan Kredit harus seimbang (balance).');
        }

        DB::transaction(function () use ($request) {
            $latest = JournalEntry::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $journalNo = 'JV-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $entry = JournalEntry::create([
                'journal_number' => $journalNo,
                'journal_date' => $request->journal_date,
                'description' => $request->description,
                'status' => 'POSTED', // Auto-post for ease of use in POS/ERP
                'created_by' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            foreach ($request->lines as $idx => $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'description' => $line['description'] ?? null,
                    'line_order' => $idx + 1,
                ]);
            }
        });

        return back()->with('success', 'Entri Jurnal berhasil disimpan.');
    }

    // Payment Methods CRUD
    public function storePaymentMethod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'method_name' => 'required|string|max:255',
            'method_type' => 'required|in:CASH,BANK,CARD,EWALLET,QRIS,OTHER',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'is_active' => 'boolean',
            'requires_reference' => 'boolean',
        ]);

        PaymentMethod::create($validated);

        return back()->with('success', 'Metode Pembayaran berhasil dibuat.');
    }

    public function updatePaymentMethod(Request $request, int $id): RedirectResponse
    {
        $method = PaymentMethod::findOrFail($id);

        $validated = $request->validate([
            'method_name' => 'string|max:255',
            'method_type' => 'in:CASH,BANK,CARD,EWALLET,QRIS,OTHER',
            'account_id' => 'exists:chart_of_accounts,id',
            'is_active' => 'boolean',
            'requires_reference' => 'boolean',
        ]);

        $method->update($validated);

        return back()->with('success', 'Metode Pembayaran berhasil diperbarui.');
    }

    public function destroyPaymentMethod(int $id): RedirectResponse
    {
        $method = PaymentMethod::findOrFail($id);
        $method->delete();

        return back()->with('success', 'Metode Pembayaran berhasil dihapus.');
    }
}
