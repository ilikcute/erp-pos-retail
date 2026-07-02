<?php

namespace App\Repositories\Eloquent\POS;

use App\Enums\POS\SessionStatus;
use App\Models\POS\CashierSession;
use App\Repositories\Contracts\POS\SessionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SessionRepository implements SessionRepositoryInterface
{
    public function findActiveSession(int $userId): ?object
    {
        return CashierSession::with(['shift', 'location', 'user'])
            ->where('user_id', $userId)
            ->where('status', SessionStatus::OPEN)
            ->first();
    }

    public function findById(int $id): ?object
    {
        return CashierSession::with(['shift', 'location', 'user', 'closedByUser'])
            ->find($id);
    }

    public function getAll(array $filters = []): Collection
    {
        $query = CashierSession::with(['shift', 'location', 'user']);

        if (! empty($filters['shift_id'])) {
            $query->where('shift_id', $filters['shift_id']);
        }
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('opened_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('opened_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('opened_at', 'desc')->get();
    }

    public function create(array $data): object
    {
        return DB::transaction(function () use ($data) {
            return CashierSession::create($data);
        });
    }

    public function update(int $id, array $data): object
    {
        return DB::transaction(function () use ($id, $data) {
            $session = CashierSession::findOrFail($id);
            $session->update($data);

            return $session->fresh();
        });
    }

    public function hasOpenSession(int $userId): bool
    {
        return CashierSession::where('user_id', $userId)
            ->where('status', SessionStatus::OPEN)
            ->exists();
    }

    /**
     * Generate session number: SES-YYYYMMDD-XXXX
     */
    public function generateSessionNo(): string
    {
        $date = now()->format('Ymd');
        $last = CashierSession::whereDate('opened_at', today())
            ->where('session_no', 'like', "SES-{$date}-%")
            ->count();

        return sprintf('SES-%s-%04d', $date, $last + 1);
    }
}
