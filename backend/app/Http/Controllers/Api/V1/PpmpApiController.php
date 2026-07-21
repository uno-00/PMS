<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PpmpResource;
use App\Models\Planning\Ppmp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PpmpApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Ppmp::class);

        $ppmps = Ppmp::query()
            ->with('division')
            ->withCount('items')
            ->when($request->filled('fiscal_year_id'), fn ($q) => $q->where('fiscal_year_id', $request->fiscal_year_id))
            ->when($request->filled('division_id'), fn ($q) => $q->where('division_id', $request->division_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return PpmpResource::collection($ppmps);
    }

    public function show(Ppmp $ppmp): PpmpResource
    {
        $this->authorize('view', $ppmp);

        return new PpmpResource($ppmp->load('division', 'items'));
    }
}
