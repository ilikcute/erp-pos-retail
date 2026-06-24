<?php

namespace App\Actions\Accounting;

use App\Enums\DocumentStatus;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\GeneralLedger;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;

class PostJournalEntryAction
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly AuditService $auditService,
    ) {}

    public function execute(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $journalNo = $this->documentNumberService->generate('JOURNAL_ENTRY');

            $journalEntry = JournalEntry::create([
                'journal_no'      => $journalNo,
                'journal_date'    => $data['journal_date'] ?? now()->toDateString(),
                'fiscal_period_id' => $data['fiscal_period_id'],
                'description'     => $data['description'],
                'status'          => DocumentStatus::POSTED->value,
                'reference_no'    => $data['reference_no'] ?? null,
                'reference_type'  => $data['reference_type'] ?? null,
                'total_debit'     => 0,
                'total_credit'    => 0,
                'created_by'      => auth()->id(),
                'posted_by'       => auth()->id(),
                'posted_at'       => now(),
            ]);

            $this->createJournalLines($journalEntry, $data['lines'] ?? []);
            $this->postGeneralLedger($journalEntry);
            $this->validateBalance($journalEntry);

            $this->auditService->log(
                module: 'Accounting',
                action: 'POST_JOURNAL_ENTRY',
                tableName: 'journal_entries',
                recordId: $journalEntry->id,
                documentNo: $journalNo,
                newValues: [
                    'fiscal_period_id' => $data['fiscal_period_id'],
                    'total_debit'      => $journalEntry->total_debit,
                    'total_credit'     => $journalEntry->total_credit,
                ],
            );

            return $journalEntry->fresh(['lines']);
        });
    }

    private function createJournalLines(JournalEntry $journalEntry, array $lines): void
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($lines as $line) {
            $journalEntry->lines()->create([
                'account_id'      => $line['account_id'],
                'debit_amount'    => $line['debit_amount'] ?? 0,
                'credit_amount'   => $line['credit_amount'] ?? 0,
                'description'     => $line['description'] ?? null,
                'created_by'      => auth()->id(),
            ]);

            $totalDebit += $line['debit_amount'] ?? 0;
            $totalCredit += $line['credit_amount'] ?? 0;
        }

        $journalEntry->update([
            'total_debit'  => $totalDebit,
            'total_credit' => $totalCredit,
        ]);
    }

    private function postGeneralLedger(JournalEntry $journalEntry): void
    {
        foreach ($journalEntry->lines as $line) {
            if ($line->debit_amount > 0) {
                GeneralLedger::create([
                    'account_id'       => $line->account_id,
                    'journal_entry_id' => $journalEntry->id,
                    'debit_amount'     => $line->debit_amount,
                    'credit_amount'    => 0,
                    'reference_date'   => $journalEntry->journal_date,
                    'created_by'       => auth()->id(),
                ]);
            }

            if ($line->credit_amount > 0) {
                GeneralLedger::create([
                    'account_id'       => $line->account_id,
                    'journal_entry_id' => $journalEntry->id,
                    'debit_amount'     => 0,
                    'credit_amount'    => $line->credit_amount,
                    'reference_date'   => $journalEntry->journal_date,
                    'created_by'       => auth()->id(),
                ]);
            }
        }
    }

    private function validateBalance(JournalEntry $journalEntry): void
    {
        if (abs($journalEntry->total_debit - $journalEntry->total_credit) > 0.01) {
            throw new \RuntimeException(
                "Journal entry is not balanced. Debit: {$journalEntry->total_debit}, Credit: {$journalEntry->total_credit}"
            );
        }
    }
}
