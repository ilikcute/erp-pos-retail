<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Auth::user()
            ->notifications()
            ->when(!$request->boolean('include_read'), fn($q) => $q->whereNull('read_at'))
            ->latest()
            ->paginate(20);

        return Inertia::render('System/Notifications/Index', [
            'notifications' => $notifications,
            'filters' => $request->only('include_read'),
        ]);
    }

    public function markAsRead(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', 'Notifikasi ditandai sebagai sudah dibaca.');
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sebagai sudah dibaca.');
    }
}
