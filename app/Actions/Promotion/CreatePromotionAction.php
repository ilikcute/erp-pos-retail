<?php

namespace App\Actions\Promotion;

use App\Enums\DocumentStatus;
use App\Models\Promotion\Promotion;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;

class CreatePromotionAction
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly AuditService $auditService,
    ) {}

    public function execute(array $data): Promotion
    {
        return DB::transaction(function () use ($data) {
            $promotionNo = $this->documentNumberService->generate('PROMOTION');

            $promotion = Promotion::create([
                'promotion_no'    => $promotionNo,
                'promotion_name'  => $data['promotion_name'],
                'promotion_type'  => $data['promotion_type'],
                'description'     => $data['description'] ?? null,
                'start_date'      => $data['start_date'],
                'end_date'        => $data['end_date'],
                'status'          => DocumentStatus::DRAFT->value,
                'is_active'       => false,
                'priority'        => $data['priority'] ?? 0,
                'notes'           => $data['notes'] ?? null,
                'created_by'      => auth()->id(),
                'updated_by'      => auth()->id(),
            ]);

            $this->createPromotionConditions($promotion, $data['conditions'] ?? []);
            $this->createPromotionRewards($promotion, $data['rewards'] ?? []);

            $this->auditService->log(
                module: 'Promotion',
                action: 'CREATE_PROMOTION',
                tableName: 'promotions',
                recordId: $promotion->id,
                documentNo: $promotionNo,
                newValues: [
                    'promotion_name' => $data['promotion_name'],
                    'promotion_type' => $data['promotion_type'],
                    'start_date'     => $data['start_date'],
                    'end_date'       => $data['end_date'],
                ],
            );

            return $promotion->fresh(['conditions', 'rewards']);
        });
    }

    private function createPromotionConditions(Promotion $promotion, array $conditions): void
    {
        foreach ($conditions as $condition) {
            $promotion->conditions()->create([
                'condition_type'  => $condition['condition_type'],
                'operator'        => $condition['operator'],
                'value'           => $condition['value'],
                'description'     => $condition['description'] ?? null,
                'created_by'      => auth()->id(),
            ]);
        }
    }

    private function createPromotionRewards(Promotion $promotion, array $rewards): void
    {
        foreach ($rewards as $reward) {
            $promotion->rewards()->create([
                'reward_type'     => $reward['reward_type'],
                'reward_value'    => $reward['reward_value'],
                'reward_unit'     => $reward['reward_unit'],
                'max_reward'      => $reward['max_reward'] ?? null,
                'description'     => $reward['description'] ?? null,
                'created_by'      => auth()->id(),
            ]);
        }
    }
}
