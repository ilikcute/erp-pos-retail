<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingRule;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalEntryLine;
use App\Models\Accounting\JournalTemplate;
use App\Models\Accounting\JournalTemplateLine;
use App\Models\Accounting\PaymentMethod;
use App\Models\Accounting\TrialBalance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AccountingController extends Controller
{
    public function index(Request $request): Response
    {
        $chartOfAccounts = ChartOfAccount::orderBy('account_code')->get();

        // 1. Journal entries with filters
        $journalQuery = JournalEntry::with('lines.account')
            ->orderBy('journal_date', 'desc');

        if ($request->filled('status')) {
            $journalQuery->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $journalQuery->where('journal_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $journalQuery->where('journal_date', '<=', $request->date_to);
        }
        if ($request->filled('reference_type')) {
            $journalQuery->where('source_type', $request->reference_type); // Mapping reference_type to source_type
        }

        $journalEntries = $journalQuery->get()
            ->map(function ($entry) {
                $totalDebit = $entry->lines->sum('debit');
                $totalCredit = $entry->lines->sum('credit');

                return array_merge($entry->toArray(), [
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                ]);
            });

        // 2. Payment methods
        $paymentMethods = PaymentMethod::all();

        // 3. Fiscal Periods
        $fiscalPeriods = FiscalPeriod::orderBy('start_date', 'desc')->get();

        // 4. Trial Balance (dynamic calculation based on selected period)
        $trialPeriodId = $request->query('trial_period_id');
        $trialBalances = [];
        if ($trialPeriodId) {
            $period = FiscalPeriod::find($trialPeriodId);
            if ($period) {
                $trialBalances = ChartOfAccount::orderBy('account_code')->get()->map(function ($acc) use ($period) {
                    $lines = JournalEntryLine::where('account_id', $acc->id)
                        ->whereHas('journal', function ($q) use ($period) {
                            $q->whereBetween('journal_date', [$period->start_date, $period->end_date])
                                ->where('status', 'POSTED');
                        })->get();

                    return [
                        'account_code' => $acc->account_code,
                        'account_name' => $acc->account_name,
                        'account_type' => $acc->account_type,
                        'debit_balance' => $lines->sum('debit'),
                        'credit_balance' => $lines->sum('credit'),
                    ];
                })->filter(fn ($tb) => $tb['debit_balance'] > 0 || $tb['credit_balance'] > 0)->values()->all();
            }
        }

        // 5. General Ledger (detailed account movements)
        $ledgerAccountId = $request->query('ledger_account_id');
        $ledgerLines = [];
        if ($ledgerAccountId) {
            $ledgerLines = JournalEntryLine::where('account_id', $ledgerAccountId)
                ->whereHas('journal', function ($q) use ($request) {
                    if ($request->filled('date_from')) {
                        $q->where('journal_date', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $q->where('journal_date', '<=', $request->date_to);
                    }
                    $q->where('status', 'POSTED');
                })
                ->with('journal')
                ->get()
                ->map(function ($line) {
                    return [
                        'date' => $line->journal->journal_date,
                        'journal_number' => $line->journal->journal_number,
                        'description' => $line->description ?: $line->journal->description,
                        'debit' => $line->debit,
                        'credit' => $line->credit,
                    ];
                });
        }

        // 6. Templates & Rules
        $journalTemplates = JournalTemplate::with('lines.account')->get();
        $accountingRules = AccountingRule::with('template')->get();

        $routeName = $request->route() ? $request->route()->getName() : null;
        $activeTab = $request->query('activeTab');
        if (! $activeTab) {
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
            'fiscalPeriods' => $fiscalPeriods,
            'trialBalances' => $trialBalances,
            'ledgerLines' => $ledgerLines,
            'journalTemplates' => $journalTemplates,
            'accountingRules' => $accountingRules,
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
            'account_code' => 'string|unique:chart_of_accounts,account_code,'.$id,
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
            $journalNo = 'JV-'.date('Ymd').'-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);

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

    public function postJournal(int $id): RedirectResponse
    {
        $entry = JournalEntry::findOrFail($id);
        $entry->update(['status' => 'POSTED', 'posted_at' => now()]);

        return back()->with('success', 'Jurnal berhasil di-post.');
    }

    public function voidJournal(int $id): RedirectResponse
    {
        $entry = JournalEntry::findOrFail($id);
        $entry->update(['status' => 'VOID']);

        return back()->with('success', 'Jurnal berhasil di-void.');
    }

    public function storeFiscalPeriod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_name' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        FiscalPeriod::create($validated);

        return back()->with('success', 'Periode fiskal berhasil dibuat.');
    }

    public function closeFiscalPeriod(int $id): RedirectResponse
    {
        $period = FiscalPeriod::findOrFail($id);
        $period->update([
            'status' => 'CLOSED',
            'closed_by' => auth()->id() ?? 1,
            'closed_at' => now(),
        ]);

        // Compile trial balance snapshot
        $accounts = ChartOfAccount::all();
        foreach ($accounts as $acc) {
            $lines = JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journal', function ($q) use ($period) {
                    $q->whereBetween('journal_date', [$period->start_date, $period->end_date])
                        ->where('status', 'POSTED');
                })->get();
            $debit = $lines->sum('debit');
            $credit = $lines->sum('credit');

            if ($debit > 0 || $credit > 0) {
                TrialBalance::updateOrCreate([
                    'fiscal_period_id' => $period->id,
                    'account_id' => $acc->id,
                ], [
                    'debit_balance' => $debit,
                    'credit_balance' => $credit,
                ]);
            }
        }

        return back()->with('success', 'Periode fiskal berhasil ditutup.');
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'template_code' => 'required|string|max:50|unique:journal_templates,template_code',
            'template_name' => 'required|string|max:255',
            'event_type' => 'required|string',
            'description' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.direction' => 'required|in:DEBIT,CREDIT',
            'lines.*.formula' => 'nullable|string',
            'lines.*.description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $tmpl = JournalTemplate::create([
                'template_code' => $validated['template_code'],
                'template_name' => $validated['template_name'],
                'event_type' => $validated['event_type'],
                'description' => $validated['description'] ?? null,
                'is_active' => true,
            ]);

            foreach ($validated['lines'] as $idx => $line) {
                JournalTemplateLine::create([
                    'journal_template_id' => $tmpl->id,
                    'account_id' => $line['account_id'],
                    'direction' => $line['direction'],
                    'formula' => $line['formula'] ?? 'grand_total',
                    'description' => $line['description'] ?? null,
                    'sort_order' => $idx + 1,
                ]);
            }
        });

        return back()->with('success', 'Template Jurnal berhasil dibuat.');
    }

    public function destroyTemplate(int $id): RedirectResponse
    {
        $tmpl = JournalTemplate::findOrFail($id);
        $tmpl->delete();

        return back()->with('success', 'Template Jurnal berhasil dihapus.');
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rule_name' => 'required|string|max:255',
            'event_type' => 'required|string',
            'journal_template_id' => 'required|exists:journal_templates,id',
        ]);

        AccountingRule::create($validated);

        return back()->with('success', 'Aturan akuntansi berhasil dibuat.');
    }
}
