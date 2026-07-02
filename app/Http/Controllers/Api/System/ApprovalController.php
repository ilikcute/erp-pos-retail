<?php

namespace App\Http\Controllers\Api\System;

use App\Events\ApprovalApproved;
use App\Events\ApprovalRejected;
use App\Http\Controllers\Controller;
use App\Models\System\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function pending(Request $request)
    {
        $approvals = Approval::with(['requestor', 'approvable'])
            ->where('status', 'PENDING')
            ->when($request->module, fn ($q) => $q->where('module', $request->module))
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $approvals,
        ]);
    }

    public function show(int $id)
    {
        $approval = Approval::with(['requestor', 'approver', 'approvable'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $approval,
        ]);
    }

    public function approve(Request $request, int $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $approval = Approval::findOrFail($id);

        if (! $approval->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Approval sudah diproses',
            ], 422);
        }

        $approval->update([
            'status' => 'APPROVED',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Trigger approval callback (optional)
        event(new ApprovalApproved($approval));

        return response()->json([
            'success' => true,
            'message' => 'Approval berhasil disetujui',
            'data' => $approval->fresh(),
        ]);
    }

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $approval = Approval::findOrFail($id);

        if (! $approval->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Approval sudah diproses',
            ], 422);
        }

        $approval->update([
            'status' => 'REJECTED',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_notes' => $validated['notes'],
        ]);

        event(new ApprovalRejected($approval));

        return response()->json([
            'success' => true,
            'message' => 'Approval ditolak',
            'data' => $approval->fresh(),
        ]);
    }
}
