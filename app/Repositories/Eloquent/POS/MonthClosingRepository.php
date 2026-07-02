<?php

namespace App\Repositories\Eloquent\POS;

use App\Enums\POS\ClosingStatus;
use App\Models\POS\MonthClosing;
use App\Repositories\Contracts\POS\MonthClosingRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonthClosingRepository implements MonthClosingRepositoryInterface
{
    public function findByPeriod(int $year, int $month, ?int $locationId = null): ?object
    {
        $query = MonthClosing::with(['location', 'closedByUser'])
            ->where('closing_year', $year)
            ->where('closing_month', $month);

        if ($locationId) {
            $query->where('location_id', $locationId);
        } else {
            $query->whereNull('location_id');
        }

        return $query->first();
    }

    public function findById(int $id): ?object
    {
        return MonthClosing::with(['location', 'closedByUser'])->find($id);
    }

    public function getAll(array $filters = []): Collection
    {
        $query = MonthClosing::with(['location', 'closedByUser']);

        if (! empty($filters['year'])) {
            $query->where('closing_year', $filters['year']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        return $query->orderBy('closing_year', 'desc')
            ->orderBy('closing_month', 'desc')
            ->get();
    }

    public function create(array $data): object
    {
        return DB::transaction(function () use ($data) {
            return MonthClosing::create($data);
        });
    }

    public function update(int $id, array $data): object
    {
        return DB::transaction(function () use ($id, $data) {
            $closing = MonthClosing::findOrFail($id);
            $closing->update($data);

            return $closing->fresh();
        });
    }

    public function isPeriodLocked(int $year, int $month, ?int $locationId = null): bool
    {
        $query = MonthClosing::where('closing_year', $year)
            ->where('closing_month', $month)
            ->whereIn('status', [ClosingStatus::CLOSED, ClosingStatus::LOCKED]);

        if ($locationId) {
            $query->where(function ($q) use ($locationId) {
                $q->where('location_id', $locationId)
                    ->orWhereNull('location_id');
            });
        }

        return $query->exists();
    }
}
