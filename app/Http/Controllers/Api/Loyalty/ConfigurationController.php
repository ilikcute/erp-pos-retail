<?php

namespace App\Http\Controllers\Api\Loyalty;

use App\Http\Controllers\Controller;
use App\Models\Loyalty\LoyaltyConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfigurationController extends Controller
{
    public function show()
    {
        return response()->json([
            'success' => true,
            'data' => LoyaltyConfiguration::getInstance(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'point_expiry_months' => 'integer|min:1',
            'minimum_redeem_points' => 'integer|min:0',
            'point_value' => 'integer|min:1',
            'earn_rate' => 'numeric|min:1',
            'allow_negative_point' => 'boolean',
            'is_enabled' => 'boolean',
        ]);

        $config = LoyaltyConfiguration::getInstance();
        $config->update(array_merge($validated, ['updated_by' => Auth::id()]));

        return response()->json([
            'success' => true,
            'data' => $config->fresh(),
            'message' => 'Konfigurasi berhasil diupdate',
        ]);
    }
}
