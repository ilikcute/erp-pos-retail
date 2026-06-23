<?php

namespace App\Services\MasterData;

use App\Models\MasterData\Customer;
use App\Repositories\Contracts\MasterData\CustomerRepositoryInterface;
use App\Support\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly AuditService $auditService,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->customerRepository->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Customer
    {
        return $this->customerRepository->findById($id);
    }

    public function create(array $data): Customer
    {
        $customer = $this->customerRepository->create($data);

        $this->auditService->log(
            module: 'MasterData',
            action: 'CREATE_CUSTOMER',
            tableName: 'customers',
            recordId: $customer->id,
            newValues: ['customer_code' => $customer->customer_code, 'customer_name' => $customer->customer_name],
        );

        return $customer;
    }

    public function update(Customer $customer, array $data): Customer
    {
        $original = $customer->only(['customer_code', 'customer_name', 'is_active']);
        $customer = $this->customerRepository->update($customer, $data);

        $this->auditService->log(
            module: 'MasterData',
            action: 'UPDATE_CUSTOMER',
            tableName: 'customers',
            recordId: $customer->id,
            oldValues: $original,
            newValues: $customer->only(['customer_code', 'customer_name', 'is_active']),
        );

        return $customer;
    }

    public function delete(Customer $customer): void
    {
        $this->auditService->log(
            module: 'MasterData',
            action: 'DELETE_CUSTOMER',
            tableName: 'customers',
            recordId: $customer->id,
            oldValues: ['customer_code' => $customer->customer_code, 'customer_name' => $customer->customer_name],
        );

        $this->customerRepository->delete($customer);
    }
}
