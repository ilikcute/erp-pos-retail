<?php

namespace App\Services\MasterData;

use App\Models\MasterData\Unit;
use App\Repositories\Contracts\MasterData\UnitRepositoryInterface;
use App\Support\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UnitService
{
    public function __construct(
        private readonly UnitRepositoryInterface $unitRepository,
        private readonly AuditService $auditService,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->unitRepository->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Unit
    {
        return $this->unitRepository->findById($id);
    }

    public function create(array $data): Unit
    {
        $unit = $this->unitRepository->create($data);

        $this->auditService->log(
            module: 'MasterData',
            action: 'CREATE_UNIT',
            tableName: 'units',
            recordId: $unit->id,
            newValues: ['unit_code' => $unit->unit_code, 'unit_name' => $unit->unit_name],
        );

        return $unit;
    }

    public function update(Unit $unit, array $data): Unit
    {
        $original = $unit->only(['unit_code', 'unit_name', 'is_active']);
        $unit = $this->unitRepository->update($unit, $data);

        $this->auditService->log(
            module: 'MasterData',
            action: 'UPDATE_UNIT',
            tableName: 'units',
            recordId: $unit->id,
            oldValues: $original,
            newValues: $unit->only(['unit_code', 'unit_name', 'is_active']),
        );

        return $unit;
    }

    public function delete(Unit $unit): void
    {
        $this->auditService->log(
            module: 'MasterData',
            action: 'DELETE_UNIT',
            tableName: 'units',
            recordId: $unit->id,
            oldValues: ['unit_code' => $unit->unit_code, 'unit_name' => $unit->unit_name],
        );

        $this->unitRepository->delete($unit);
    }
}
