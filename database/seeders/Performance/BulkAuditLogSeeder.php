<?php

namespace Database\Seeders\Performance;

use Database\Seeders\Performance\Concerns\SeedsPerformanceData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BulkAuditLogSeeder extends Seeder
{
    use SeedsPerformanceData;

    /** @var array<int, array{module: string, action: string, table: string}> */
    private array $templates = [
        ['module' => 'MasterData', 'action' => 'customer.created', 'table' => 'customers'],
        ['module' => 'MasterData', 'action' => 'customer.updated', 'table' => 'customers'],
        ['module' => 'MasterData', 'action' => 'supplier.created', 'table' => 'suppliers'],
        ['module' => 'Product', 'action' => 'product.created', 'table' => 'products'],
        ['module' => 'Product', 'action' => 'product.updated', 'table' => 'products'],
        ['module' => 'Pricing', 'action' => 'price_list.updated', 'table' => 'price_list_items'],
        ['module' => 'POS', 'action' => 'transaction.posted', 'table' => 'sales_transactions'],
        ['module' => 'POS', 'action' => 'transaction.voided', 'table' => 'sales_transactions'],
        ['module' => 'System', 'action' => 'user.updated', 'table' => 'users'],
        ['module' => 'System', 'action' => 'setting.updated', 'table' => 'system_settings'],
    ];

    public function run(): void
    {
        $count = $this->performanceCount();

        $userId = DB::table('users')->where('email', 'superadmin@system.local')->value('id') ?? 1;
        $rows = [];

        for ($i = 1; $i <= $count; $i++) {
            $template = $this->templates[($i - 1) % count($this->templates)];
            $createdAt = now()->subMinutes($count - $i);

            $rows[] = [
                'user_id' => $userId,
                'module' => $template['module'],
                'action' => $template['action'],
                'table_name' => $template['table'],
                'record_id' => (string) (($i % 500) + 1),
                'document_no' => 'PERF-DOC-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'document_type' => strtoupper($template['module']),
                'status_before' => $i % 3 === 0 ? 'DRAFT' : null,
                'status_after' => $i % 3 === 0 ? 'POSTED' : null,
                'old_values' => json_encode(['sample' => 'old-'.$i]),
                'new_values' => json_encode(['sample' => 'new-'.$i]),
                'reason' => 'Dummy audit log for API performance testing',
                'ip_address' => '127.0.0.'.(($i % 200) + 1),
                'user_agent' => 'PerformanceSeeder/1.0',
                'created_at' => $createdAt,
            ];
        }

        $this->insertInChunks('audit_logs', $rows);

        $this->command->info("✅ {$count} bulk audit logs berhasil di-seed.");
    }
}
