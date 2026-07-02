<?php

namespace App\Http\Controllers\Api\POS;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Promotion\PromotionRepositoryInterface;
use Illuminate\Http\Request;

class PosPromotionController extends Controller
{
    public function __construct(
        private readonly PromotionRepositoryInterface $promoRepo
    ) {}

    /**
     * Daftar promo aktif untuk ditampilkan di POS
     */
    public function active(Request $request)
    {
        $promotions = $this->promoRepo->findActivePromotions();

        $data = $promotions->map(fn ($promo) => [
            'id' => $promo->id,
            'code' => $promo->promotion_code,
            'name' => $promo->promotion_name,
            'description' => $promo->description,
            'valid_from' => $promo->valid_from->format('Y-m-d H:i'),
            'valid_until' => $promo->valid_until->format('Y-m-d H:i'),
            'rewards' => $promo->rewards->map(fn ($r) => [
                'type' => $r->reward_type->value,
                'value' => $r->reward_value,
                'label' => $this->formatRewardLabel($r),
            ]),
            'targets' => $promo->targets->map(fn ($t) => [
                'type' => $t->target_type->value,
                'id' => $t->target_id,
            ]),
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    private function formatRewardLabel($reward): string
    {
        return match ($reward->reward_type->value) {
            'PERCENTAGE' => "Diskon {$reward->reward_value}%",
            'FIXED_AMOUNT' => 'Potongan Rp '.number_format($reward->reward_value, 0, ',', '.'),
            'FREE_PRODUCT' => 'Gratis Produk',
            'SPECIAL_PRICE' => 'Harga Khusus Rp '.number_format($reward->reward_value, 0, ',', '.'),
            default => 'Promo',
        };
    }
}
