<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ScenarioRequest;
use App\Models\Analysis;
use App\Services\AnalysisHistoryService;
use Illuminate\Http\RedirectResponse;

class ScenarioController extends Controller
{
    public function store(ScenarioRequest $request, Analysis $analysis, AnalysisHistoryService $history): RedirectResponse
    {
        $scenario = $history->simulate($analysis, $request->scenarioInput());

        return redirect()->route('web.analyses.show', ['analysis' => $analysis, 'scenario' => $scenario->id])
            ->with('status', 'What-if scenario saved. Your original comparison stays unchanged.');
    }
}
