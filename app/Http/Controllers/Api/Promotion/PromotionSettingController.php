<?php

namespace App\Http\Controllers\Api\Promotion;

use App\Http\Controllers\Controller;
use App\Models\Promotion\PromotionSetting;
use Illuminate\Http\Request;

class PromotionSettingController extends Controller
{
    public function show()
    {
        $settings = PromotionSetting::getInstance();

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'margin_protection_mode' => 'in:BLOCK,WARNING,ALLOW',
            'allow_negative_margin' => 'boolean',
            'allow_stacking' => 'boolean',
            'max_stacking_promotions' => 'integer|min:1',
        ]);

        $settings = PromotionSetting::getInstance();
        $settings->update(array_merge($validated, [
            'updated_by' => auth()->id(),
        ]));

        return response()->json([
            'success' => true,
            'data' => $settings->fresh(),
            'message' => 'Pengaturan promosi berhasil diupdate',
        ]);
    }
}
