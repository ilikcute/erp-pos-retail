<?php

namespace Database\Seeders\System;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── General ──────────────────────────────────────────────
            ['key' => 'app.name',       'value' => 'POS ERP',       'type' => 'string',  'group' => 'general', 'description' => 'Nama aplikasi'],
            ['key' => 'app.currency',   'value' => 'IDR',           'type' => 'string',  'group' => 'general', 'description' => 'Mata uang utama'],
            ['key' => 'app.timezone',   'value' => 'Asia/Jakarta',  'type' => 'string',  'group' => 'general', 'description' => 'Timezone'],
            ['key' => 'app.locale',     'value' => 'id',            'type' => 'string',  'group' => 'general', 'description' => 'Bahasa default'],
            // ── POS ───────────────────────────────────────────────────
            ['key' => 'pos.receipt_footer',         'value' => 'Terima kasih telah berbelanja!', 'type' => 'string', 'group' => 'pos', 'description' => 'Footer struk'],
            ['key' => 'pos.allow_negative_stock',   'value' => 'false', 'type' => 'boolean', 'group' => 'pos', 'description' => 'Izinkan stok negatif di POS'],
            ['key' => 'pos.require_customer',       'value' => 'false', 'type' => 'boolean', 'group' => 'pos', 'description' => 'Wajib isi customer di setiap transaksi'],
            ['key' => 'pos.auto_print_receipt',     'value' => 'true',  'type' => 'boolean', 'group' => 'pos', 'description' => 'Auto print struk setelah transaksi'],
            // ── Inventory ─────────────────────────────────────────────
            ['key' => 'inventory.valuation_method', 'value' => 'FIFO', 'type' => 'string', 'group' => 'inventory', 'description' => 'Metode penilaian stok (FIFO|AVERAGE)'],
            ['key' => 'inventory.low_stock_alert',  'value' => 'true',  'type' => 'boolean', 'group' => 'inventory', 'description' => 'Aktifkan alert stok rendah'],
            // ── Approval ─────────────────────────────────────────────
            ['key' => 'approval.purchase_order.min_amount', 'value' => '0',    'type' => 'integer', 'group' => 'approval', 'description' => 'Minimal amount PO yang butuh approval (0 = semua)'],
            ['key' => 'approval.purchase_order.enabled',    'value' => 'true', 'type' => 'boolean', 'group' => 'approval', 'description' => 'Aktifkan approval untuk Purchase Order'],
            // ── Tax ───────────────────────────────────────────────────
            ['key' => 'tax.default_tax_code', 'value' => 'PPN11', 'type' => 'string', 'group' => 'tax', 'description' => 'Kode pajak default'],
        ];

        foreach ($settings as $setting) {
            $setting['created_at'] = now();
            $setting['updated_at'] = now();
        }

        DB::table('system_settings')->insertOrIgnore($settings);
    }
}
