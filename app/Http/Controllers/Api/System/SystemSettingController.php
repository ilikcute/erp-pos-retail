<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use App\Models\System\SystemSetting;
use App\Support\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('system.setting.view');

        $settings = SystemSetting::query()
            ->when($request->group, fn ($q, $g) => $q->where('group', $g))
            ->orderBy('group')->orderBy('key')
            ->get()
            ->map(fn ($s) => [
                'key' => $s->key,
                'value' => $s->getTypedValue(),
                'type' => $s->type,
                'group' => $s->group,
                'description' => $s->description,
            ]);

        return response()->json(['data' => $settings]);
    }

    public function show(string $key): JsonResponse
    {
        $this->authorize('system.setting.view');

        $setting = SystemSetting::where('key', $key)->first();
        abort_if(! $setting, 404, 'Setting tidak ditemukan.');

        return response()->json(['data' => [
            'key' => $setting->key,
            'value' => $setting->getTypedValue(),
            'type' => $setting->type,
            'group' => $setting->group,
            'description' => $setting->description,
        ]]);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $this->authorize('system.setting.manage');

        $setting = SystemSetting::where('key', $key)->first();
        abort_if(! $setting, 404, 'Setting tidak ditemukan.');

        $request->validate(['value' => ['required']]);

        $oldValue = $setting->value;
        SystemSetting::setValue($key, $request->value);

        $this->auditService->log(
            module: 'System',
            action: 'UPDATE_SETTING',
            tableName: 'system_settings',
            recordId: $key,
            oldValues: ['value' => $oldValue],
            newValues: ['value' => $request->value],
        );

        return response()->json(['message' => "Setting '{$key}' berhasil diperbarui."]);
    }

    public function updateBulk(Request $request): JsonResponse
    {
        $this->authorize('system.setting.manage');

        $request->validate(['settings' => ['required', 'array']]);

        foreach ($request->settings as $key => $value) {
            SystemSetting::setValue($key, $value);
        }

        $this->auditService->log('System', 'BULK_UPDATE_SETTINGS', 'system_settings', 'bulk', [], $request->settings);

        return response()->json(['message' => count($request->settings).' settings berhasil diperbarui.']);
    }
}
