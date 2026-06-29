<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Promotion\PromotionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PromotionController extends Controller
{
    public function __construct(
        private readonly PromotionRepositoryInterface $promoRepo
    ) {}

    public function index(Request $request): Response
    {
        $user = Auth::user();
        
        // Hanya user dengan role tertentu yang boleh melihat halaman promosi
        if (!$user->hasAnyRole(['superadmin', 'admin', 'manager', 'supervisor', 'kasir'])) {
            abort(403, 'Unauthorized');
        }

        $promotions = $this->promoRepo->getAll($request->only('status'));

        return Inertia::render('Promotion/Index', [
            'promotions' => $promotions,
            'filters' => $request->only('status'),
        ]);
    }
}
