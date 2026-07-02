<?php

namespace App\Actions\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OptimizeQueryCacheAction
{
    public function cacheProductWithVariants(int $productId): array
    {
        return Cache::remember("product.{$productId}.variants", 3600, function () use ($productId) {
            return DB::table('products')
                ->where('id', $productId)
                ->with(['variants', 'category', 'brand'])
                ->first();
        });
    }

    public function cachePriceList(int $priceListId): array
    {
        return Cache::remember("price_list.{$priceListId}", 1800, function () use ($priceListId) {
            return DB::table('price_lists')
                ->where('id', $priceListId)
                ->with('items')
                ->first();
        });
    }

    public function cacheActivePromotions(?string $date = null): array
    {
        $date = $date ?? now()->toDateString();

        return Cache::remember("promotions.active.{$date}", 3600, function () use ($date) {
            return DB::table('promotions')
                ->where('is_active', true)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->with(['conditions', 'rewards'])
                ->get()
                ->toArray();
        });
    }

    public function invalidateProductCache(int $productId): void
    {
        Cache::forget("product.{$productId}.variants");
    }

    public function invalidatePriceListCache(int $priceListId): void
    {
        Cache::forget("price_list.{$priceListId}");
    }

    public function invalidatePromotionCache(?string $date = null): void
    {
        $date = $date ?? now()->toDateString();
        Cache::forget("promotions.active.{$date}");
    }

    public function warmUpCache(): array
    {
        $stats = [
            'products_cached' => 0,
            'price_lists_cached' => 0,
            'promotions_cached' => 0,
        ];

        $products = DB::table('products')->limit(100)->get();
        foreach ($products as $product) {
            $this->cacheProductWithVariants($product->id);
            $stats['products_cached']++;
        }

        $priceLists = DB::table('price_lists')->where('is_active', true)->get();
        foreach ($priceLists as $priceList) {
            $this->cachePriceList($priceList->id);
            $stats['price_lists_cached']++;
        }

        $this->cacheActivePromotions();
        $stats['promotions_cached']++;

        return $stats;
    }
}
