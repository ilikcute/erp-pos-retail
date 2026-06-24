<?php

namespace App\Repositories\Contracts\Inventory;

use App\Models\Inventory\InventoryLedger;

interface InventoryLedgerRepositoryInterface
{
    public function create(array $data): InventoryLedger;

    public function findById(int $id): ?InventoryLedger;

    public function getByDocumentType(string $documentType, int $documentId);

    public function getProductLedger(int $productId, int $variantId = null);

    public function getMovementsByLocation(int $locationId);
}
