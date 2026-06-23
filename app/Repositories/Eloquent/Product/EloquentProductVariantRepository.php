<?php

namespace App\Repositories\Eloquent\Product;

use App\Models\Product\ProductVariant;
use App\Repositories\Contracts\Product\ProductVariantRepositoryInterface;

class EloquentProductVariantRepository implements ProductVariantRepositoryInterface
{
    public function findById(int $id): ?ProductVariant
    {
        return ProductVariant::find($id);
    }

    public function findByBarcode(string $barcode): ?ProductVariant
    {
        return ProductVariant::whereHas(
            'barcodes',
            fn($q) =>
            $q->where('barcode', $barcode)
        )->with(['product', 'barcodes', 'costProfile'])->first();
    }

    public function create(array $data): ProductVariant
    {
        return ProductVariant::create($data);
    }

    public function update(ProductVariant $variant, array $data): ProductVariant
    {
        $variant->update($data);
        return $variant->fresh();
    }

    public function delete(ProductVariant $variant): void
    {
        $variant->delete();
    }
}
