<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $approvals = Approval::with(['requestor', 'approvable'])
            ->where('status', 'PENDING')
            ->when($request->module, fn($q) => $q->where('module', $request->module))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('System/Approvals/Index', [
            'approvals' => $approvals,
            'filters' => $request->only('module'),
        ]);
    }

    public function approve(Request $request, int $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $approval = Approval::findOrFail($id);

        if (!$approval->isPending()) {
            return back()->with('error', 'Approval sudah diproses sebelumnya.');
        }

        $approval->update([
            'status' => 'APPROVED',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Trigger approval callback
        event(new \App\Events\ApprovalApproved($approval));

        return back()->with('success', 'Approval berhasil disetujui.');
    }

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $approval = Approval::findOrFail($id);

        if (!$approval->isPending()) {
            return back()->with('error', 'Approval sudah diproses sebelumnya.');
        }

        $approval->update([
            'status' => 'REJECTED',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_notes' => $validated['notes'],
        ]);

        event(new \App\Events\ApprovalRejected($approval));

        return back()->with('success', 'Approval berhasil ditolak.');
    }
}
