<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\AuditLog;
use App\Models\System\BusinessProfile;
use App\Models\System\DocumentType;
use App\Models\System\SystemSetting;
use App\Repositories\Contracts\System\RoleRepositoryInterface;
use App\Repositories\Contracts\System\UserRepositoryInterface;
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
            ->get();

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
}
