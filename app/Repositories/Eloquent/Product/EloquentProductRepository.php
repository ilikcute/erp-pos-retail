<?php

namespace App\Repositories\Eloquent\Product;

use App\Models\Product\Product;
use App\Repositories\Contracts\Product\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with(['brand', 'category', 'defaultVariant.primaryBarcode'])
            ->when(
                $filters['search'] ?? null,
                fn ($q, $s) => $q->where(
                    fn ($q) => $q
                        ->where('product_name', 'like', "%{$s}%")
                        ->orWhere('product_code', 'like', "%{$s}%")
                        ->orWhereHas(
                            'variants',
                            fn ($q) => $q->where('sku', 'like', "%{$s}%")
                        )
                )
            )
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->when($filters['brand_id'] ?? null, fn ($q, $v) => $q->where('brand_id', $v))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->when(isset($filters['is_sellable']), fn ($q) => $q->where('is_sellable', $filters['is_sellable']))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Product
    {
        return Product::with([
            'brand',
            'category',
            'variants.barcodes',
            'variants.variantAttributes.attribute',
            'variants.variantAttributes.value',
            'images',
            'attributes.values',
            'supplierLinks.supplier',
        ])->find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function getAll(): Collection
    {
        return Product::all();
    }

    public function findByBarcode(string $barcode): ?Product
    {
        return Product::whereHas('variants.barcodes', function ($query) use ($barcode) {
            $query->where('barcode', $barcode);
        })->first();
    }
}
