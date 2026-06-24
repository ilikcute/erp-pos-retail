<?php

namespace App\Repositories\Eloquent\Inventory;

use App\Models\Inventory\InventoryLedger;
use App\Repositories\Contracts\Inventory\InventoryLedgerRepositoryInterface;

class EloquentInventoryLedgerRepository implements InventoryLedgerRepositoryInterface
{
    public function create(array $data): InventoryLedger
    {
        return InventoryLedger::create($data);
    }

    public function findById(int $id): ?InventoryLedger
    {
        return InventoryLedger::find($id);
    }

    public function getByDocumentType(string $documentType, int $documentId)
    {
        return InventoryLedger::where('document_type', $documentType)
            ->where('document_id', $documentId)
            ->get();
    }

    public function getProductLedger(int $productId, int $variantId = null)
    {
        $query = InventoryLedger::where('product_id', $productId);

        if ($variantId) {
            $query->where('product_variant_id', $variantId);
        }

        return $query->orderBy('reference_date')->get();
    }

    public function getMovementsByLocation(int $locationId)
    {
        return InventoryLedger::where('location_id', $locationId)
            ->orderBy('reference_date', 'desc')
            ->get();
    }
}
