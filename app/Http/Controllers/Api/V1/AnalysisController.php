<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalysisRequest;
use App\Http\Requests\ListRequest;
use App\Http\Resources\AnalysisResource;
use App\Models\Analysis;
use App\Models\Device;
use App\Services\AnalysisHistoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class AnalysisController extends Controller
{
    public function index(ListRequest $request): AnonymousResourceCollection
    {
        $query = Analysis::query()->whereHas('device', fn ($query) => $query->where('user_id', $request->user()->id))->withCount('scenarios');
        if ($request->has('device_id')) {
            $query->where('device_id', $request->validated('device_id'));
        }

        return AnalysisResource::collection($query->latest('id')->paginate($request->perPage())->withQueryString());
    }

    public function store(AnalysisRequest $request, Device $device, AnalysisHistoryService $history): AnalysisResource
    {
        return new AnalysisResource($history->analyze($device, $request->validated())->loadCount('scenarios'));
    }

    public function show(Analysis $analysis): AnalysisResource
    {
        Gate::authorize('view', $analysis);

        return new AnalysisResource($analysis->loadCount('scenarios'));
    }
}
