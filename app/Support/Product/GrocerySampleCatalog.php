<?php

namespace App\Support\Product;

/**
 * Katalog sample produk grocery Indonesia untuk template import.
 * User dapat mengedit baris-baris ini sebelum upload.
 */
class GrocerySampleCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function samples(): array
    {
        return [
            ['product_code' => 'GRY-001', 'product_name' => 'Indomie Goreng Spesial 85g', 'brand_code' => 'IND', 'category_code' => 'FD', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-001', 'barcode' => '8886013850111', 'purchase_price' => 2500, 'retail_price' => 3500, 'description' => 'Mie instan goreng rasa spesial'],
            ['product_code' => 'GRY-002', 'product_name' => 'Indomie Soto Mie 77g', 'brand_code' => 'IND', 'category_code' => 'FD', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-002', 'barcode' => '8886013850128', 'purchase_price' => 2400, 'retail_price' => 3400, 'description' => 'Mie instan kuah soto'],
            ['product_code' => 'GRY-003', 'product_name' => 'Sarimi Ayam Bawang 68g', 'brand_code' => 'IND', 'category_code' => 'FD', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-003', 'barcode' => '8886013850203', 'purchase_price' => 1800, 'retail_price' => 2800, 'description' => 'Mie instan ayam bawang'],
            ['product_code' => 'GRY-004', 'product_name' => 'Beras Premium 5kg', 'brand_code' => 'IND', 'category_code' => 'FD', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-004', 'barcode' => '8991001001001', 'purchase_price' => 65000, 'retail_price' => 72000, 'description' => 'Beras putih premium kemasan 5kg'],
            ['product_code' => 'GRY-005', 'product_name' => 'Minyak Goreng Bimoli 2L', 'brand_code' => 'UNI', 'category_code' => 'FD', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-005', 'barcode' => '8991001002008', 'purchase_price' => 32000, 'retail_price' => 36500, 'description' => 'Minyak goreng sawit kemasan 2 liter'],
            ['product_code' => 'GRY-006', 'product_name' => 'Gula Pasir Gulaku 1kg', 'brand_code' => 'UNI', 'category_code' => 'FD', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-006', 'barcode' => '8991001003005', 'purchase_price' => 14500, 'retail_price' => 16500, 'description' => 'Gula pasir putih kemasan 1kg'],
            ['product_code' => 'GRY-007', 'product_name' => 'Teh Celup Sariwangi 25s', 'brand_code' => 'UNI', 'category_code' => 'FD', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-007', 'barcode' => '8991001004002', 'purchase_price' => 6500, 'retail_price' => 8500, 'description' => 'Teh celup melati isi 25 sachet'],
            ['product_code' => 'GRY-008', 'product_name' => 'Kopi Kapal Api Special 165g', 'brand_code' => 'UNI', 'category_code' => 'FD', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-008', 'barcode' => '8991001005009', 'purchase_price' => 22000, 'retail_price' => 26500, 'description' => 'Kopi bubuk robusta premium'],
            ['product_code' => 'GRY-009', 'product_name' => 'Coca Cola Kaleng 330ml', 'brand_code' => 'COC', 'category_code' => 'BV', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-009', 'barcode' => '8886000101011', 'purchase_price' => 4000, 'retail_price' => 6000, 'description' => 'Minuman bersoda kaleng 330ml'],
            ['product_code' => 'GRY-010', 'product_name' => 'Sprite Kaleng 330ml', 'brand_code' => 'COC', 'category_code' => 'BV', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-010', 'barcode' => '8886000101028', 'purchase_price' => 4000, 'retail_price' => 6000, 'description' => 'Minuman lemon-lime kaleng 330ml'],
            ['product_code' => 'GRY-011', 'product_name' => 'Fanta Orange 390ml', 'brand_code' => 'COC', 'category_code' => 'BV', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-011', 'barcode' => '8886000101035', 'purchase_price' => 4500, 'retail_price' => 6500, 'description' => 'Minuman rasa jeruk botol 390ml'],
            ['product_code' => 'GRY-012', 'product_name' => 'Air Mineral Aqua 600ml', 'brand_code' => 'UNI', 'category_code' => 'BV', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-012', 'barcode' => '8991001006006', 'purchase_price' => 2500, 'retail_price' => 4000, 'description' => 'Air mineral kemasan botol 600ml'],
            ['product_code' => 'GRY-013', 'product_name' => 'Susu UHT Full Cream 1L', 'brand_code' => 'UNI', 'category_code' => 'BV', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-013', 'barcode' => '8991001007003', 'purchase_price' => 16000, 'retail_price' => 18500, 'description' => 'Susu UHT full cream 1 liter'],
            ['product_code' => 'GRY-014', 'product_name' => 'Lifebuoy Sabun Batang 85g', 'brand_code' => 'UNI', 'category_code' => 'PC', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-014', 'barcode' => '8991001008000', 'purchase_price' => 3500, 'retail_price' => 5000, 'description' => 'Sabun mandi batang anti bakteri'],
            ['product_code' => 'GRY-015', 'product_name' => 'Clear Shampoo Menthol 170ml', 'brand_code' => 'UNI', 'category_code' => 'PC', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-015', 'barcode' => '8991001009007', 'purchase_price' => 18000, 'retail_price' => 24000, 'description' => 'Shampoo anti ketombe menthol'],
            ['product_code' => 'GRY-016', 'product_name' => 'Pepsodent Pasta Gigi 190g', 'brand_code' => 'UNI', 'category_code' => 'PC', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-016', 'barcode' => '8991001010003', 'purchase_price' => 12000, 'retail_price' => 15500, 'description' => 'Pasta gigi fluoride kemasan 190g'],
            ['product_code' => 'GRY-017', 'product_name' => 'Rinso Deterjen Bubuk 800g', 'brand_code' => 'UNI', 'category_code' => 'PC', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-017', 'barcode' => '8991001011000', 'purchase_price' => 15000, 'retail_price' => 18500, 'description' => 'Deterjen bubuk anti noda'],
            ['product_code' => 'GRY-018', 'product_name' => 'Sunlight Sabun Cuci Piring 755ml', 'brand_code' => 'UNI', 'category_code' => 'PC', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-018', 'barcode' => '8991001012007', 'purchase_price' => 14000, 'retail_price' => 17500, 'description' => 'Sabun cuci piring jeruk nipis'],
            ['product_code' => 'GRY-019', 'product_name' => 'Chitato Rasa Sapi Panggang 68g', 'brand_code' => 'IND', 'category_code' => 'FD', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-019', 'barcode' => '8991001013004', 'purchase_price' => 8500, 'retail_price' => 11000, 'description' => 'Keripik kentang rasa sapi panggang'],
            ['product_code' => 'GRY-020', 'product_name' => 'Roma Biskuit Kelapa 300g', 'brand_code' => 'IND', 'category_code' => 'FD', 'unit_code' => 'PCS', 'sku' => 'SKU-GRY-020', 'barcode' => '8991001014001', 'purchase_price' => 9000, 'retail_price' => 11500, 'description' => 'Biskuit kelapa kemasan 300g'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function templateHeaders(): array
    {
        return [
            'product_code',
            'product_name',
            'product_type',
            'brand_code',
            'category_code',
            'unit_code',
            'description',
            'is_active',
            'is_sellable',
            'is_purchasable',
            'track_stock',
            'min_stock',
            'max_stock',
            'reorder_point',
            'sku',
            'barcode',
            'barcode_type',
            'purchase_price',
            'retail_price',
        ];
    }
}
