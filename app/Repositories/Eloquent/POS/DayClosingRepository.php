<?php

namespace App\Repositories\Eloquent\POS;

use App\Models\POS\DayClosing;
use App\Repositories\Contracts\POS\DayClosingRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DayClosingRepository implements DayClosingRepositoryInterface
{
    public function findByDate(string $date, ?int $locationId = null): ?object
    {
        $query = DayClosing::with(['location', 'closedByUser', 'sessions.user'])
            ->whereDate('closing_date', $date);

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query->first();
    }

    public function findById(int $id): ?object
    {
        return DayClosing::with(['location', 'closedByUser', 'sessions.user', 'transactions'])
            ->find($id);
    }

    public function getAll(array $filters = []): Collection
    {
        $query = DayClosing::with(['location', 'closedByUser']);

        if (! empty($filters['date_from'])) {
            $query->whereDate('closing_date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('closing_date', '<=', $filters['date_to']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        return $query->orderBy('closing_date', 'desc')->get();
    }

    public function create(array $data): object
    {
        return DB::transaction(function () use ($data) {
            return DayClosing::create($data);
        });
    }

    public function update(int $id, array $data): object
    {
        return DB::transaction(function () use ($id, $data) {
            $closing = DayClosing::findOrFail($id);
            $closing->update($data);

            return $closing->fresh();
        });
    }

    public function generateClosingNumber(): string
    {
        $date = now()->format('Ymd');
        $last = DayClosing::whereDate('closing_date', today())
            ->where('closing_number', 'like', "DC-{$date}-%")
            ->count();

        return sprintf('DC-%s-%04d', $date, $last + 1);
    }
}
