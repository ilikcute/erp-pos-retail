<?php

namespace App\Services\POS;

use App\Models\POS\Shift;
use App\Repositories\Contracts\POS\ShiftRepositoryInterface;
use App\Support\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ShiftService
{
    public function __construct(
        private readonly ShiftRepositoryInterface $shiftRepository,
        private readonly AuditService $auditService,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->shiftRepository->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Shift
    {
        return $this->shiftRepository->findById($id);
    }

    public function findActive(): Collection
    {
        return $this->shiftRepository->findActive();
    }

    public function create(array $data): Shift
    {
        $shift = $this->shiftRepository->create($data);

        $this->auditService->log(
            module: 'POS',
            action: 'CREATE_SHIFT',
            tableName: 'shifts',
            recordId: $shift->id,
            newValues: ['shift_code' => $shift->shift_code, 'shift_name' => $shift->shift_name],
        );

        return $shift;
    }

    public function update(Shift $shift, array $data): Shift
    {
        $original = $shift->only(['shift_name', 'start_time', 'end_time', 'is_active']);

        $shift = $this->shiftRepository->update($shift, $data);

        $this->auditService->log(
            module: 'POS',
            action: 'UPDATE_SHIFT',
            tableName: 'shifts',
            recordId: $shift->id,
            oldValues: $original,
            newValues: $shift->only(['shift_name', 'start_time', 'end_time', 'is_active']),
        );

        return $shift;
    }

    public function delete(Shift $shift): void
    {
        $this->auditService->log(
            module: 'POS',
            action: 'DELETE_SHIFT',
            tableName: 'shifts',
            recordId: $shift->id,
            oldValues: ['shift_code' => $shift->shift_code, 'shift_name' => $shift->shift_name],
        );

        $this->shiftRepository->delete($shift);
    }
}
