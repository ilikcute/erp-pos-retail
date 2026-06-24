<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PromotionController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('pricing.price-list.view');

        $promotions = collect([
            [
                'id' => 1,
                'promotion_name' => 'Grand Opening Discount',
                'description' => 'Diskon 10% untuk semua item tanpa minimal pembelian dalam rangka pembukaan outlet baru.',
                'status' => 'ACTIVE',
                'start_date' => '2026-06-01',
                'end_date' => '2026-07-31',
                'rewards' => [
                    [
                        'id' => 1,
                        'reward_type' => 'DISCOUNT',
                        'reward_value' => 10,
                        'reward_unit' => 'PERCENTAGE',
                    ]
                ]
            ],
            [
                'id' => 2,
                'promotion_name' => 'Weekend Flash Sale',
                'description' => 'Potongan langsung Rp 50.000 untuk pembelian produk kategori fashion minimal Rp 300.000.',
                'status' => 'ACTIVE',
                'start_date' => '2026-06-20',
                'end_date' => '2026-07-10',
                'rewards' => [
                    [
                        'id' => 2,
                        'reward_type' => 'DIRECT_DISCOUNT',
                        'reward_value' => 50000,
                        'reward_unit' => 'CURRENCY',
                    ]
                ]
            ],
            [
                'id' => 3,
                'promotion_name' => 'Buy 1 Get 1 Free (Selected Items)',
                'description' => 'Beli 1 gratis 1 untuk produk makanan ringan tertentu setiap hari Jumat.',
                'status' => 'DRAFT',
                'start_date' => '2026-07-01',
                'end_date' => '2026-08-31',
                'rewards' => [
                    [
                        'id' => 3,
                        'reward_type' => 'FREE_ITEM',
                        'reward_value' => 1,
                        'reward_unit' => 'PCS',
                    ]
                ]
            ]
        ]);

        return Inertia::render('Promotion/Index', [
            'promotions' => $promotions,
        ]);
    }
}
