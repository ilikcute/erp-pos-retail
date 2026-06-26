<?php

namespace App\Services\Product;

use App\Support\Product\GrocerySampleCatalog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class ProductImportService
{
    /**
     * @return array{brands: array<string, int>, categories: array<string, int>, units: array<string, int>, retail_price_list_id: ?int}
     */
    public function referenceMaps(): array
    {
        return [
            'brands' => DB::table('product_brands')->pluck('id', 'brand_code')->all(),
            'categories' => DB::table('product_categories')->pluck('id', 'category_code')->all(),
            'units' => DB::table('units')->pluck('id', 'unit_code')->all(),
            'retail_price_list_id' => DB::table('price_lists')
                ->where('price_list_code', 'RETAIL-DEFAULT')
                ->value('id'),
        ];
    }

    public function buildTemplateZipPath(): string
    {
        $tempPath = storage_path('app/temp/product_import_template_'.now()->timestamp.'.zip');

        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file template ZIP.');
        }

        $zip->addFromString('product_import_template.csv', $this->buildProductCsv());
        $zip->addFromString('referensi_brands.csv', $this->buildReferenceCsv(['brand_code', 'brand_name'], 'product_brands', ['brand_code', 'brand_name']));
        $zip->addFromString('referensi_categories.csv', $this->buildReferenceCsv(['category_code', 'category_name'], 'product_categories', ['category_code', 'category_name']));
        $zip->addFromString('referensi_units.csv', $this->buildReferenceCsv(['unit_code', 'unit_name'], 'units', ['unit_code', 'unit_name']));
        $zip->addFromString('PANDUAN.txt', $this->buildGuideText());
        $zip->close();

        return $tempPath;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function parseCsvFile(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            throw new \RuntimeException('File CSV tidak dapat dibaca.');
        }

        $headers = null;
        $rows = [];
        $lineNumber = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if ($this->isEmptyRow($data)) {
                continue;
            }

            if ($headers === null) {
                $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $data);

                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = isset($data[$index]) ? trim((string) $data[$index]) : '';
            }

            if ($this->isEmptyAssocRow($row)) {
                continue;
            }

            $row['_line'] = (string) $lineNumber;
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @param  array<string, string>  $row
     * @param  array{brands: array<string, int>, categories: array<string, int>, units: array<string, int>}  $maps
     * @return array{data: array<string, mixed>, errors: array<int, string>}
     */
    public function mapRow(array $row, array $maps): array
    {
        $errors = [];

        if ($row['product_code'] === '') {
            $errors[] = 'product_code wajib diisi';
        }
        if ($row['product_name'] === '') {
            $errors[] = 'product_name wajib diisi';
        }
        if ($row['category_code'] === '') {
            $errors[] = 'category_code wajib diisi';
        }
        if ($row['unit_code'] === '') {
            $errors[] = 'unit_code wajib diisi';
        }

        $categoryId = $maps['categories'][$row['category_code']] ?? null;
        if ($row['category_code'] !== '' && ! $categoryId) {
            $errors[] = "category_code '{$row['category_code']}' tidak ditemukan";
        }

        $unitId = $maps['units'][$row['unit_code']] ?? null;
        if ($row['unit_code'] !== '' && ! $unitId) {
            $errors[] = "unit_code '{$row['unit_code']}' tidak ditemukan";
        }

        $brandId = null;
        if ($row['brand_code'] !== '') {
            $brandId = $maps['brands'][$row['brand_code']] ?? null;
            if (! $brandId) {
                $errors[] = "brand_code '{$row['brand_code']}' tidak ditemukan";
            }
        }

        if ($errors !== []) {
            return ['data' => [], 'errors' => $errors];
        }

        $productType = strtoupper($row['product_type'] ?: 'SIMPLE');
        if (! in_array($productType, ['SIMPLE', 'VARIANT', 'BUNDLE'], true)) {
            $errors[] = 'product_type harus SIMPLE, VARIANT, atau BUNDLE';

            return ['data' => [], 'errors' => $errors];
        }

        if ($productType !== 'SIMPLE') {
            $errors[] = 'Import CSV saat ini hanya mendukung product_type SIMPLE';

            return ['data' => [], 'errors' => $errors];
        }

        $sku = $row['sku'] !== '' ? $row['sku'] : $row['product_code'];

        return [
            'data' => [
                'product_code' => $row['product_code'],
                'product_name' => $row['product_name'],
                'product_type' => 'SIMPLE',
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'base_unit_id' => $unitId,
                'description' => $row['description'] ?: null,
                'is_active' => $this->toBool($row['is_active'] ?? '1', true),
                'is_sellable' => $this->toBool($row['is_sellable'] ?? '1', true),
                'is_purchasable' => $this->toBool($row['is_purchasable'] ?? '1', true),
                'track_stock' => $this->toBool($row['track_stock'] ?? '1', true),
                'min_stock' => $this->toDecimal($row['min_stock'] ?? '0'),
                'max_stock' => $this->toDecimal($row['max_stock'] ?? '0'),
                'reorder_point' => $this->toDecimal($row['reorder_point'] ?? '0'),
                'default_variant' => [
                    'sku' => $sku,
                    'barcode' => $row['barcode'] ?: null,
                    'barcode_type' => strtoupper($row['barcode_type'] ?: 'EAN13'),
                    'purchase_price' => $this->toDecimal($row['purchase_price'] ?? '0'),
                ],
                'retail_price' => $this->toDecimal($row['retail_price'] ?? '0'),
            ],
            'errors' => [],
        ];
    }

    private function buildProductCsv(): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, GrocerySampleCatalog::templateHeaders());

        foreach (GrocerySampleCatalog::samples() as $sample) {
            fputcsv($handle, [
                $sample['product_code'],
                $sample['product_name'],
                'SIMPLE',
                $sample['brand_code'],
                $sample['category_code'],
                $sample['unit_code'],
                $sample['description'],
                '1',
                '1',
                '1',
                '1',
                '10',
                '500',
                '20',
                $sample['sku'],
                $sample['barcode'],
                'EAN13',
                $sample['purchase_price'],
                $sample['retail_price'],
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $content;
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string>  $columns
     */
    private function buildReferenceCsv(array $headers, string $table, array $columns): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        DB::table($table)
            ->orderBy($columns[0])
            ->get($columns)
            ->each(function ($row) use ($handle, $columns) {
                fputcsv($handle, [(string) $row->{$columns[0]}, (string) $row->{$columns[1]}]);
            });

        rewind($handle);
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $content;
    }

    private function buildGuideText(): string
    {
        return <<<'GUIDE'
PANDUAN IMPORT PRODUK GROCERY
=============================

Isi file ZIP:
1. product_import_template.csv  — file utama yang diedit & di-upload
2. referensi_brands.csv         — daftar kode brand yang valid
3. referensi_categories.csv     — daftar kode kategori yang valid
4. referensi_units.csv          — daftar kode satuan yang valid

Langkah:
1. Extract ZIP, buka product_import_template.csv di Excel/LibreOffice
2. Edit baris sample grocery atau tambah baris produk baru
3. Pastikan brand_code, category_code, unit_code sesuai file referensi
4. Simpan sebagai CSV (UTF-8)
5. Upload file CSV melalui menu Import di halaman Products

Kolom wajib:
- product_code, product_name, category_code, unit_code

Kolom opsional:
- brand_code, sku, barcode, purchase_price, retail_price, description
- is_active, is_sellable, is_purchasable, track_stock (isi 1 atau 0)

Catatan:
- Saat ini import CSV mendukung product_type SIMPLE saja
- retail_price otomatis ditambahkan ke price list RETAIL-DEFAULT jika ada
- Hapus baris sample yang tidak diperlukan sebelum upload
GUIDE;
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function isEmptyAssocRow(array $row): bool
    {
        foreach ($row as $key => $value) {
            if ($key === '_line') {
                continue;
            }
            if (trim($value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function toBool(string $value, bool $default): bool
    {
        if ($value === '') {
            return $default;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'ya', 'y'], true);
    }

    private function toDecimal(string $value): float
    {
        if ($value === '') {
            return 0.0;
        }

        return (float) str_replace(',', '', $value);
    }
}
