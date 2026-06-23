<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreCustomerRequest;
use App\Http\Requests\MasterData\UpdateCustomerRequest;
use App\Http\Resources\MasterData\CustomerResource;
use App\Models\MasterData\Customer;
use App\Services\MasterData\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('master-data.customer.view');

        $customers = $this->customerService->paginate(
            filters: $request->only(['search', 'is_active', 'customer_category_id', 'city']),
            perPage: $request->integer('per_page', 15),
        );

        return response()->json([
            'data' => CustomerResource::collection($customers->items()),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page'    => $customers->lastPage(),
                'per_page'     => $customers->perPage(),
                'total'        => $customers->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('master-data.customer.view');

        $customer = $this->customerService->findById($id);
        abort_if(! $customer, 404, 'Customer tidak ditemukan.');

        return response()->json(['data' => new CustomerResource($customer->load('category'))]);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerService->create($request->validated());

        return response()->json([
            'data'    => new CustomerResource($customer),
            'message' => 'Customer berhasil ditambahkan.',
        ], 201);
    }

    public function update(UpdateCustomerRequest $request, int $id): JsonResponse
    {
        $customer = $this->customerService->findById($id);
        abort_if(! $customer, 404, 'Customer tidak ditemukan.');

        $customer = $this->customerService->update($customer, $request->validated());

        return response()->json([
            'data'    => new CustomerResource($customer),
            'message' => 'Customer berhasil diperbarui.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('master-data.customer.delete');

        $customer = $this->customerService->findById($id);
        abort_if(! $customer, 404, 'Customer tidak ditemukan.');

        $this->customerService->delete($customer);

        return response()->json(['message' => 'Customer berhasil dihapus.']);
    }
}
