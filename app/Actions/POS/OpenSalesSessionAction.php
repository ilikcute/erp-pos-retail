<?php

namespace App\Actions\POS;

use App\Enums\POS\SessionStatus;
use App\Models\POS\CashierSession;
use App\Models\POS\Shift;
use App\Models\System\User;
use App\Notifications\POS\SessionOpenedNotification;
use App\Repositories\Contracts\POS\SessionRepositoryInterface;
use App\Support\AuditService;
use Illuminate\Support\Facades\DB;

class OpenSalesSessionAction
{
    public function __construct(
        private readonly SessionRepositoryInterface $sessionRepository,
        private readonly AuditService $auditService,
    ) {}

    public function execute(int $userId, int $shiftId, array $data): CashierSession
    {
        return DB::transaction(function () use ($userId, $shiftId, $data) {
            // Validasi: tidak boleh ada sesi aktif untuk user ini
            if ($this->sessionRepository->hasOpenSession($userId)) {
                throw new \DomainException('Anda sudah memiliki sesi aktif. Tutup sesi sebelumnya terlebih dahulu.');
            }

            $shift = Shift::findOrFail($shiftId);

            if (! $shift->is_active) {
                throw new \DomainException('Shift tidak aktif.');
            }

            $sessionNo = $this->sessionRepository->generateSessionNo();

            /** @var CashierSession $session */
            $session = $this->sessionRepository->create([
                'session_no' => $sessionNo,
                'shift_id' => $shiftId,
                'user_id' => $userId,
                'location_id' => $data['location_id'] ?? null,
                'opening_cash' => $data['opening_cash'] ?? 0,
                'expected_cash' => 0,
                'total_sales' => 0,
                'total_transactions' => 0,
                'status' => SessionStatus::OPEN,
                'notes' => $data['notes'] ?? null,
                'opened_at' => now(),
            ]);

            $this->auditService->log(
                module: 'POS',
                action: 'OPEN_CASHIER_SESSION',
                tableName: 'cashier_sessions',
                recordId: $session->id,
                documentNo: $sessionNo,
                statusAfter: SessionStatus::OPEN->value,
                newValues: [
                    'session_no' => $sessionNo,
                    'user_id' => $userId,
                    'shift_id' => $shiftId,
                    'location_id' => $data['location_id'] ?? null,
                    'opening_cash' => $data['opening_cash'] ?? 0,
                    'status' => SessionStatus::OPEN->value,
                ],
            );

            $this->auditService->activity(
                activity: 'OPEN_SESSION',
                module: 'POS',
                description: "Membuka sesi kasir {$sessionNo} dengan modal awal Rp ".number_format($data['opening_cash'] ?? 0, 0, ',', '.'),
            );

            // ─── Notifikasi ke User ───────────────────────────────────
            $user = User::find($userId);
            if ($user) {
                $user->notify(new SessionOpenedNotification($session));
            }

            return $session;
        });
    }
}
