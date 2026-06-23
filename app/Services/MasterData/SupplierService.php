<?php

namespace App\Services\MasterData;

use App\Models\MasterData\Supplier;
use App\Repositories\Contracts\MasterData\SupplierRepositoryInterface;
use App\Support\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SupplierService
{
    public function __construct(
        private readonly SupplierRepositoryInterface $supplierRepository,
        private readonly AuditService $auditService,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->supplierRepository->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Supplier
    {
        return $this->supplierRepository->findById($id);
    }

    public function listActive(): Collection
    {
        return $this->supplierRepository->listActive();
    }

    public function create(array $data): Supplier
    {
        $supplier = $this->supplierRepository->create($data);

        $this->auditService->log(
            module: 'MasterData',
            action: 'CREATE_SUPPLIER',
            tableName: 'suppliers',
            recordId: $supplier->id,
            newValues: ['supplier_code' => $supplier->supplier_code, 'supplier_name' => $supplier->supplier_name],
        );

        return $supplier;
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $original = $supplier->only(['supplier_code', 'supplier_name', 'is_active']);

        $supplier = $this->supplierRepository->update($supplier, $data);

        $this->auditService->log(
            module: 'MasterData',
            action: 'UPDATE_SUPPLIER',
            tableName: 'suppliers',
            recordId: $supplier->id,
            oldValues: $original,
            newValues: $supplier->only(['supplier_code', 'supplier_name', 'is_active']),
        );

        return $supplier;
    }

    public function delete(Supplier $supplier): void
    {
        $this->auditService->log(
            module: 'MasterData',
            action: 'DELETE_SUPPLIER',
            tableName: 'suppliers',
            recordId: $supplier->id,
            oldValues: ['supplier_code' => $supplier->supplier_code, 'supplier_name' => $supplier->supplier_name],
        );

        $this->supplierRepository->delete($supplier);
    }
}
