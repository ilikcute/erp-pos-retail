<?php

namespace App\Services\Product;

use App\Enums\ProductType;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Repositories\Contracts\Product\ProductRepositoryInterface;
use App\Repositories\Contracts\Product\ProductVariantRepositoryInterface;
use App\Support\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly AuditService $auditService,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    public function findByBarcode(string $barcode): ?ProductVariant
    {
        return $this->productVariantRepository->findByBarcode($barcode);
    }

    /**
     * Buat produk beserta default variant (untuk SIMPLE type).
     */
    public function create(array $data): Product
    {
        $variantData = $data['default_variant'] ?? [];
        $attributeData = $data['attributes'] ?? [];
        $variantsInput = $data['variants'] ?? [];
        unset($data['default_variant'], $data['attributes'], $data['variants']);

        return DB::transaction(function () use ($data, $variantData, $attributeData, $variantsInput) {
            $product = $this->productRepository->create($data);

            // Buat default variant untuk produk SIMPLE
            if ($product->product_type === ProductType::SIMPLE || ! $product->hasVariants()) {
                $variant = $product->variants()->create(array_merge([
                    'sku' => $variantData['sku'] ?? $product->product_code,
                    'variant_name' => $product->product_name,
                    'is_default' => true,
                    'is_active' => true,
                    'created_by' => $data['created_by'] ?? auth()->id(),
                    'updated_by' => $data['updated_by'] ?? auth()->id(),
                ], $variantData));

                // Buat barcode default jika disertakan
                if ($variantData['barcode'] ?? null) {
                    $variant->barcodes()->create([
                        'barcode' => $variantData['barcode'],
                        'barcode_type' => $variantData['barcode_type'] ?? 'EAN13',
                        'is_primary' => true,
                        'created_by' => $data['created_by'] ?? auth()->id(),
                        'updated_by' => $data['updated_by'] ?? auth()->id(),
                    ]);
                }
            } else {
                // Tipe VARIANT: Buat attributes, values, dan variants
                $attributeMap = [];
                $valueMap = [];

                // 1. Simpan Product Attributes dan Values
                foreach ($attributeData as $index => $attr) {
                    $attribute = $product->attributes()->create([
                        'attribute_name' => $attr['attribute_name'],
                        'sort_order' => $index,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);

                    $attributeMap[$attr['attribute_name']] = $attribute->id;

                    foreach ($attr['values'] as $valIndex => $valString) {
                        $value = $attribute->values()->create([
                            'value' => $valString,
                            'sort_order' => $valIndex,
                        ]);
                        $valueMap[$attr['attribute_name']][$valString] = $value->id;
                    }
                }

                // 2. Simpan Variants
                foreach ($variantsInput as $vInput) {
                    $barcodeData = $vInput['barcode'] ?? null;
                    $attrValues = $vInput['attribute_values'] ?? [];
                    unset($vInput['barcode'], $vInput['attribute_values']);

                    $variant = $product->variants()->create(array_merge($vInput, [
                        'is_default' => false,
                        'is_active' => $vInput['is_active'] ?? true,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]));

                    if ($barcodeData) {
                        $variant->barcodes()->create([
                            'barcode' => $barcodeData,
                            'barcode_type' => $vInput['barcode_type'] ?? 'EAN13',
                            'is_primary' => true,
                            'created_by' => auth()->id(),
                            'updated_by' => auth()->id(),
                        ]);
                    }

                    // Hubungkan dengan attribute values
                    foreach ($attrValues as $av) {
                        $attrName = $av['attribute_name'] ?? null;
                        $valString = $av['value'] ?? null;

                        if ($attrName && $valString && isset($valueMap[$attrName][$valString])) {
                            $variant->variantAttributes()->create([
                                'attribute_id' => $attributeMap[$attrName],
                                'attribute_value_id' => $valueMap[$attrName][$valString],
                            ]);
                        }
                    }
                }
            }

            $this->auditService->log(
                module: 'Product',
                action: 'CREATE_PRODUCT',
                tableName: 'products',
                recordId: $product->id,
                newValues: ['product_code' => $product->product_code, 'product_name' => $product->product_name],
            );

            return $product->load(['variants.barcodes', 'brand', 'category']);
        });
    }

    public function update(Product $product, array $data): Product
    {
        $original = $product->only(['product_code', 'product_name', 'is_active', 'is_sellable']);

        $product = $this->productRepository->update($product, $data);

        $this->auditService->log(
            module: 'Product',
            action: 'UPDATE_PRODUCT',
            tableName: 'products',
            recordId: $product->id,
            oldValues: $original,
            newValues: $product->only(['product_code', 'product_name', 'is_active', 'is_sellable']),
        );

        return $product->load(['variants.barcodes', 'brand', 'category']);
    }

    public function delete(Product $product): void
    {
        abort_if(
            $product->variants()->whereHas('priceListItems')->exists(),
            422,
            'Produk masih memiliki harga aktif di price list.'
        );

        $this->auditService->log(
            module: 'Product',
            action: 'DELETE_PRODUCT',
            tableName: 'products',
            recordId: $product->id,
            oldValues: ['product_code' => $product->product_code, 'product_name' => $product->product_name],
        );

        $this->productRepository->delete($product);
    }

    /**
     * Tambah varian ke produk VARIANT type.
     */
    public function addVariant(Product $product, array $variantData): ProductVariant
    {
        abort_if(! $product->hasVariants(), 422, 'Produk ini bukan tipe VARIANT.');

        return DB::transaction(function () use ($product, $variantData) {
            $barcodeData = $variantData['barcode'] ?? null;
            $attributeData = $variantData['attributes'] ?? [];
            unset($variantData['barcode'], $variantData['attributes']);

            $variant = $product->variants()->create(array_merge($variantData, [
                'is_default' => false,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]));

            if ($barcodeData) {
                $variant->barcodes()->create([
                    'barcode' => $barcodeData,
                    'barcode_type' => $variantData['barcode_type'] ?? 'EAN13',
                    'is_primary' => true,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }

            foreach ($attributeData as $attr) {
                $variant->variantAttributes()->create($attr);
            }

            $this->auditService->log('Product', 'ADD_VARIANT', 'product_variants', $variant->id);

            return $variant->load('barcodes', 'variantAttributes');
        });
    }

    public function updateVariant(ProductVariant $variant, array $data): ProductVariant
    {
        return DB::transaction(function () use ($variant, $data) {
            $barcodeData = $data['barcode'] ?? null;
            unset($data['barcode']);

            $variant = $this->productVariantRepository->update($variant, $data);

            if ($barcodeData !== null) {
                $variant->barcodes()->updateOrCreate(
                    ['is_primary' => true],
                    [
                        'barcode' => $barcodeData,
                        'barcode_type' => $data['barcode_type'] ?? 'EAN13',
                        'updated_by' => auth()->id(),
                        'created_by' => auth()->id(),
                    ]
                );
            }

            $this->auditService->log('Product', 'UPDATE_VARIANT', 'product_variants', $variant->id);

            return $variant->load('barcodes', 'variantAttributes');
        });
    }

    public function deleteVariant(ProductVariant $variant): void
    {
        abort_if(
            $variant->priceListItems()->exists(),
            422,
            'Varian ini masih memiliki harga aktif di price list.'
        );

        $this->auditService->log('Product', 'DELETE_VARIANT', 'product_variants', $variant->id);

        $this->productVariantRepository->delete($variant);
    }
}
