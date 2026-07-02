<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\CreateTransferRequest;
use App\Http\Resources\Inventory\TransferResource;
use App\Models\Inventory\InventoryTransfer;
use App\Services\Inventory\TransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferController extends Controller
{
    public function __construct(private readonly TransferService $transferService) {}

    public function index(Request $request)
    {
        $query = InventoryTransfer::with(['source', 'destination', 'items.variant'])
            ->when($request->source_location_id, fn ($q) => $q->where('source_location_id', $request->source_location_id))
            ->when($request->destination_location_id, fn ($q) => $q->where('destination_location_id', $request->destination_location_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn ($q) => $q->whereDate('transfer_date', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('transfer_date', '<=', $request->date_to))
            ->latest();

        return TransferResource::collection($query->paginate(20));
    }

    public function store(CreateTransferRequest $request)
    {
        $transfer = $this->transferService->create($request->validated(), Auth::id());

        return new TransferResource($transfer);
    }

    public function post(int $id)
    {
        $transfer = $this->transferService->post($id, Auth::id());

        return new TransferResource($transfer);
    }
}
