<?php

namespace App\Services\Pricing;

use App\Models\Pricing\PriceList;
use App\Models\Pricing\PriceListItem;
use App\Repositories\Contracts\MasterData\CustomerRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceListRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceListItemRepositoryInterface;

/**
 * PriceResolverService
 *
 * Menentukan harga jual berdasarkan prioritas:
 *   1. Price list yang mapped ke customer category
 *   2. Default price list (RETAIL)
 *
 * Dipakai oleh POS saat kasir memilih produk.
 */
class PriceResolverService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly PriceListRepositoryInterface $priceListRepository,
        private readonly PriceListItemRepositoryInterface $priceListItemRepository,
    ) {}

    /**
     * Resolve harga untuk satu variant.
     *
     * @param  int       $variantId
     * @param  int|null  $customerId    — jika ada customer, cek customer category price list
     * @param  int|null  $unitId        — jika null, pakai unit default variant
     * @param  float     $qty           — untuk tiered pricing
     * @return array{price: float, price_list_id: int, price_list_name: string, unit_id: int}
     * @throws \RuntimeException jika tidak ada harga ditemukan
     */
    public function resolve(
        int $variantId,
        ?int $customerId = null,
        ?int $unitId = null,
        float $qty = 1.0
    ): array {
        // 1. Coba price list dari customer category
        if ($customerId) {
            $price = $this->resolveByCustomer($variantId, $customerId, $unitId, $qty);
            if ($price) return $price;
        }

        // 2. Fallback ke default RETAIL price list
        $price = $this->resolveDefault($variantId, $unitId, $qty);
        if ($price) return $price;

        throw new \RuntimeException(
            "Tidak ada harga ditemukan untuk variant ID {$variantId}."
        );
    }

    /**
     * Resolve berdasarkan customer category price list.
     */
    private function resolveByCustomer(
        int $variantId,
        int $customerId,
        ?int $unitId,
        float $qty
    ): ?array {
        $customer = $this->customerRepository->findById($customerId);
        if (! $customer?->customer_category_id) return null;

        // Ambil price list yang mapped ke customer category ini
        $priceLists = $this->priceListRepository->getActiveMappedToCategory($customer->customer_category_id);

        foreach ($priceLists as $priceList) {
            $item = $this->priceListItemRepository->findItem($priceList->id, $variantId, $unitId, $qty);
            if ($item) {
                return $this->formatResult($item, $priceList);
            }
        }

        return null;
    }

    /**
     * Resolve dari default RETAIL price list.
     */
    private function resolveDefault(int $variantId, ?int $unitId, float $qty): ?array
    {
        $defaultList = $this->priceListRepository->findActiveDefaultRetail();
        if (! $defaultList) return null;

        $item = $this->priceListItemRepository->findItem($defaultList->id, $variantId, $unitId, $qty);
        if (! $item) return null;

        return $this->formatResult($item, $defaultList);
    }

    private function formatResult(PriceListItem $item, PriceList $priceList): array
    {
        return [
            'price'           => (float) $item->price,
            'price_list_id'   => $priceList->id,
            'price_list_name' => $priceList->price_list_name,
            'price_list_type' => $priceList->price_list_type->value,
            'unit_id'         => $item->unit_id,
            'min_qty'         => (float) $item->min_qty,
        ];
    }

    /**
     * Resolve batch — untuk list produk di POS atau laporan.
     *
     * @param  array<int>  $variantIds
     * @param  int|null    $customerId
     * @return array<int, array>   key = variant_id
     */
    public function resolveBatch(array $variantIds, ?int $customerId = null): array
    {
        $result = [];
        foreach ($variantIds as $variantId) {
            try {
                $result[$variantId] = $this->resolve($variantId, $customerId);
            } catch (\RuntimeException) {
                $result[$variantId] = null;
            }
        }
        return $result;
    }
}
