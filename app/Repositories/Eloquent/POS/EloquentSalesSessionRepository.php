<?php

namespace App\Repositories\Eloquent\POS;

use App\Models\POS\SalesSession;
use App\Repositories\Contracts\POS\SalesSessionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentSalesSessionRepository implements SalesSessionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return SalesSession::query()
            ->with(['shift', 'cashier'])
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where('session_no', 'like', "%{$search}%");
            })
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['cashier_id'] ?? null, fn ($q, $cashierId) => $q->where('cashier_id', $cashierId))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->where('session_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->where('session_date', '<=', $date))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?SalesSession
    {
        return SalesSession::with(['shift', 'cashier'])->find($id);
    }

    public function findOpenByCashier(int $cashierId): ?SalesSession
    {
        return SalesSession::where('cashier_id', $cashierId)
            ->where('status', 'OPEN')
            ->latest()
            ->first();
    }

    public function findOpenByDate(Carbon $date): Collection
    {
        return SalesSession::where('session_date', $date->toDateString())
            ->where('status', 'OPEN')
            ->get();
    }

    public function create(array $data): SalesSession
    {
        return SalesSession::create($data);
    }

    public function update(SalesSession $session, array $data): SalesSession
    {
        $session->update($data);

        return $session->fresh();
    }

    public function closeSession(SalesSession $session, array $closingData): SalesSession
    {
        $session->update(array_merge($closingData, [
            'status' => 'CLOSED',
            'closed_at' => now(),
        ]));

        return $session->fresh();
    }
}
