<?php

namespace App\Repositories\Contracts\MasterData;

use App\Models\MasterData\CustomerCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CustomerCategoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?CustomerCategory;

    public function create(array $data): CustomerCategory;

    public function update(CustomerCategory $category, array $data): CustomerCategory;

    public function delete(CustomerCategory $category): void;

    public function listAll(): Collection;
}
