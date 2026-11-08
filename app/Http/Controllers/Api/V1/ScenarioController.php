<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListRequest;
use App\Http\Requests\ScenarioRequest;
use App\Http\Resources\ScenarioResource;
use App\Models\Analysis;
use App\Models\Scenario;
use App\Services\AnalysisHistoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ScenarioController extends Controller
{
    public function index(ListRequest $request, Analysis $analysis): AnonymousResourceCollection
    {
        Gate::authorize('view', $analysis);

        return ScenarioResource::collection($analysis->scenarios()->latest('id')->paginate($request->perPage())->withQueryString());
    }

    public function store(ScenarioRequest $request, Analysis $analysis, AnalysisHistoryService $history): ScenarioResource
    {
        return new ScenarioResource($history->simulate($analysis, $request->validated())->load('analysis'));
    }

    public function show(Scenario $scenario): ScenarioResource
    {
        $scenario->load('analysis.device');
        Gate::authorize('view', $scenario);

        return new ScenarioResource($scenario);
    }
}
