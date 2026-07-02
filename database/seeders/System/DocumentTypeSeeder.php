<?php

namespace Database\Seeders\System;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // POS
            ['code' => 'SALES_SESSION',     'name' => 'Sales Session',            'prefix' => 'SS',   'date_format' => 'Ymd', 'padding' => 4, 'separator' => '-'],
            ['code' => 'SALES_TRANSACTION', 'name' => 'Sales Transaction',        'prefix' => 'POS',  'date_format' => 'Ymd', 'padding' => 4, 'separator' => '-'],
            ['code' => 'SALES_PAYMENT',     'name' => 'Sales Payment',            'prefix' => 'PAY',  'date_format' => 'Ymd', 'padding' => 4, 'separator' => '-'],
            ['code' => 'SALES_HOLD',        'name' => 'Sales Hold Bill',          'prefix' => 'HLD',  'date_format' => 'Ymd', 'padding' => 4, 'separator' => '-'],
            ['code' => 'SALES_RETURN',      'name' => 'Sales Return',             'prefix' => 'RTN',  'date_format' => 'Ymd', 'padding' => 4, 'separator' => '-'],
            // Inventory
            ['code' => 'STOCK_ADJUSTMENT',  'name' => 'Stock Adjustment',         'prefix' => 'ADJ',  'date_format' => 'Ymd', 'padding' => 4, 'separator' => '-'],
            ['code' => 'STOCK_OPNAME',      'name' => 'Stock Opname',             'prefix' => 'OPN',  'date_format' => 'Ymd', 'padding' => 4, 'separator' => '-'],
            ['code' => 'STOCK_TRANSFER',    'name' => 'Stock Transfer',           'prefix' => 'TRF',  'date_format' => 'Ymd', 'padding' => 4, 'separator' => '-'],
            // Purchasing
            ['code' => 'PURCHASE_REQUEST',  'name' => 'Purchase Request',          'prefix' => 'PRQ',  'date_format' => 'Ym',  'padding' => 4, 'separator' => '-'],
            ['code' => 'PURCHASE_ORDER',    'name' => 'Purchase Order',           'prefix' => 'PO',   'date_format' => 'Ym',  'padding' => 4, 'separator' => '-'],
            ['code' => 'GOODS_RECEIPT',     'name' => 'Goods Receipt',            'prefix' => 'GR',   'date_format' => 'Ymd', 'padding' => 4, 'separator' => '-'],
            ['code' => 'PURCHASE_INVOICE',  'name' => 'Purchase Invoice',         'prefix' => 'PI',   'date_format' => 'Ym',  'padding' => 4, 'separator' => '-'],
            ['code' => 'PURCHASE_RETURN',   'name' => 'Purchase Return',          'prefix' => 'PR',   'date_format' => 'Ymd', 'padding' => 4, 'separator' => '-'],
            // Accounting
            ['code' => 'JOURNAL_ENTRY',     'name' => 'Journal Entry',            'prefix' => 'JE',   'date_format' => 'Ymd', 'padding' => 4, 'separator' => '-'],
            ['code' => 'PAYMENT_VOUCHER',   'name' => 'Payment Voucher',          'prefix' => 'PV',   'date_format' => 'Ymd', 'padding' => 4, 'separator' => '-'],
            ['code' => 'RECEIPT_VOUCHER',   'name' => 'Receipt Voucher',          'prefix' => 'RV',   'date_format' => 'Ymd', 'padding' => 4, 'separator' => '-'],
        ];

        foreach ($types as &$type) {
            $type['suffix'] = null;
            $type['is_active'] = true;
            $type['created_at'] = now();
            $type['updated_at'] = now();
        }

        DB::table('document_types')->insertOrIgnore($types);

        $this->command->info('✅ Document types berhasil di-seed ('.count($types).' tipe).');
    }
}
