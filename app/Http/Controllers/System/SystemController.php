<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\AuditLog;
use App\Models\System\BusinessProfile;
use App\Models\System\DocumentType;
use App\Models\System\SystemSetting;
use App\Repositories\Contracts\System\RoleRepositoryInterface;
use App\Repositories\Contracts\System\UserRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SystemController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly RoleRepositoryInterface $roleRepository
    ) {}

    public function index(): Response
    {
        $this->authorize('system.setting.view');

        $users = $this->userRepository->listAll();
        $roles = $this->roleRepository->listAll();

        $settings = SystemSetting::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        $documentTypes = DocumentType::query()
            ->orderBy('name')
            ->paginate(15, ['*'], 'doc_page');

        $businessProfile = BusinessProfile::first();

        $auditLogs = AuditLog::with('user')
            ->latest()
            ->paginate(15, ['*'], 'audit_page');

        return Inertia::render('System/Index', [
            'users' => $users,
            'roles' => $roles,
            'settings' => $settings,
            'documentTypes' => $documentTypes,
            'businessProfile' => $businessProfile,
            'auditLogs' => $auditLogs,
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $this->authorize('system.setting.manage');

        $request->validate([
            'settings' => 'required|array',
            'settings.*.id' => 'required|exists:system_settings,id',
            'settings.*.value' => 'required|string',
        ]);

        foreach ($request->settings as $settingData) {
            $setting = SystemSetting::findOrFail($settingData['id']);
            $setting->update(['value' => $settingData['value']]);
        }

        return back()->with('success', 'Konfigurasi sistem berhasil diperbarui.');
    }

    public function updateBusinessProfile(Request $request): RedirectResponse
    {
        $this->authorize('system.setting.manage');

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:100',
        ]);

        $profile = BusinessProfile::first();
        if ($profile) {
            $profile->update($validated);
        } else {
            BusinessProfile::create($validated);
        }

        return back()->with('success', 'Profil Perusahaan berhasil diperbarui.');
    }
}
