<?php

namespace App\Services\MasterData;

use App\Models\MasterData\Tax;
use App\Repositories\Contracts\MasterData\TaxRepositoryInterface;
use App\Support\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaxService
{
    public function __construct(
        private readonly TaxRepositoryInterface $taxRepository,
        private readonly AuditService $auditService,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->taxRepository->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Tax
    {
        return $this->taxRepository->findById($id);
    }

    public function create(array $data): Tax
    {
        $tax = $this->taxRepository->create($data);

        $this->auditService->log(
            module: 'MasterData',
            action: 'CREATE_TAX',
            tableName: 'taxes',
            recordId: $tax->id,
            newValues: ['tax_code' => $tax->tax_code, 'tax_rate' => $tax->tax_rate],
        );

        return $tax;
    }

    public function update(Tax $tax, array $data): Tax
    {
        $original = $tax->only(['tax_code', 'tax_name', 'tax_rate', 'is_active']);
        $tax = $this->taxRepository->update($tax, $data);

        $this->auditService->log(
            module: 'MasterData',
            action: 'UPDATE_TAX',
            tableName: 'taxes',
            recordId: $tax->id,
            oldValues: $original,
            newValues: $tax->only(['tax_code', 'tax_name', 'tax_rate', 'is_active']),
        );

        return $tax;
    }

    public function delete(Tax $tax): void
    {
        $this->auditService->log(
            module: 'MasterData',
            action: 'DELETE_TAX',
            tableName: 'taxes',
            recordId: $tax->id,
            oldValues: ['tax_code' => $tax->tax_code, 'tax_name' => $tax->tax_name],
        );

        $this->taxRepository->delete($tax);
    }
}
