<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseRequestResource;
use App\Models\Procurement\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PurchaseRequestApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        $prs = PurchaseRequest::query()
            ->with('division')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return PurchaseRequestResource::collection($prs);
    }

    public function show(PurchaseRequest $purchaseRequest): PurchaseRequestResource
    {
        $this->authorize('view', $purchaseRequest);

        return new PurchaseRequestResource($purchaseRequest->load('division', 'items'));
    }
}
