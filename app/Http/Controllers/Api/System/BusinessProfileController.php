<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use App\Models\System\BusinessProfile;
use App\Support\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessProfileController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function show(): JsonResponse
    {
        $profile = BusinessProfile::first();

        return response()->json(['data' => $profile]);
    }

    public function update(Request $request): JsonResponse
    {
        $this->authorize('system.setting.manage');

        $validated = $request->validate([
            'business_name' => ['sometimes', 'string', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'tax_id' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'website' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:50'],
        ]);

        $validated['updated_by'] = auth()->id();

        $profile = BusinessProfile::first();

        if ($profile) {
            $original = $profile->only(array_keys($validated));
            $profile->update($validated);
        } else {
            $profile = BusinessProfile::create($validated);
            $original = [];
        }

        $this->auditService->log('System', 'UPDATE_BUSINESS_PROFILE', 'business_profiles', $profile->id, $original, $validated);

        return response()->json([
            'data' => $profile->fresh(),
            'message' => 'Profil bisnis berhasil diperbarui.',
        ]);
    }
}
