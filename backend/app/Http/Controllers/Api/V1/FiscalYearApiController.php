<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FiscalYearResource;
use App\Models\Settings\FiscalYear;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FiscalYearApiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return FiscalYearResource::collection(
            FiscalYear::query()->orderByDesc('year')->paginate(20)
        );
    }
}
