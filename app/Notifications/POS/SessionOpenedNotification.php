<?php

namespace App\Notifications\POS;

use App\Models\POS\CashierSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class SessionOpenedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly CashierSession $session,
    ) {}

    /**
     * Saluran pengiriman notifikasi.
     * Pakai 'database' agar tersimpan ke tabel notifications.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data yang disimpan ke kolom `data` di tabel notifications.
     * Laravel menyimpan seluruh toArray() ke kolom `data`.
     * Kolom `title` dan `message` diisi via afterSave hook di toDatabase().
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title'        => '✅ Sesi Kasir Dibuka',
            'message'      => "Sesi {$this->session->session_no} berhasil dibuka dengan modal Rp " . number_format((float) $this->session->opening_cash, 0, ',', '.'),
            'session_id'   => $this->session->id,
            'session_no'   => $this->session->session_no,
            'opening_cash' => $this->session->opening_cash,
            'shift_id'     => $this->session->shift_id,
            'location_id'  => $this->session->location_id,
            'opened_at'    => $this->session->opened_at?->toDateTimeString(),
        ];
    }

    /**
     * Array representation (fallback).
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
