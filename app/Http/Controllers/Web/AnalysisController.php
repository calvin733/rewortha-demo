<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AnalysisRequest;
use App\Http\Requests\Web\HistoryRequest;
use App\Models\Analysis;
use App\Services\AnalysisHistoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AnalysisController extends Controller
{
    public function index(HistoryRequest $request): View
    {
        $query = Analysis::query()
            ->whereHas('device', fn ($query) => $query->where('user_id', $request->user()->id))
            ->withCount('scenarios');
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->validated('device_id'));
        }

        return view('analyses.index', [
            'analyses' => $query->latest('id')->paginate(15)->withQueryString(),
            'devices' => $request->user()->devices()->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $devices = $request->user()->devices()->whereNull('archived_at')->orderBy('name')->get();
        if ($devices->isEmpty()) {
            return redirect()->route('web.devices.create')->with('status', 'Add an active device first, then compare repair and replacement.');
        }
        $selectedDeviceId = $request->old('device_id', $request->query('device_id'));

        return view('analyses.create', [
            'devices' => $devices,
            'selectedDeviceId' => is_scalar($selectedDeviceId) ? (string) $selectedDeviceId : '',
        ]);
    }

    public function store(AnalysisRequest $request, AnalysisHistoryService $history): RedirectResponse
    {
        $device = $request->user()->devices()->findOrFail($request->validated('device_id'));
        Gate::authorize('view', $device);
        $analysis = $history->analyze($device, $request->analysisInput());

        return redirect()->route('web.analyses.show', $analysis)->with('status', 'Comparison saved. Here’s how your options add up.');
    }

    public function show(Request $request, Analysis $analysis): View
    {
        Gate::authorize('view', $analysis);
        $selectedScenario = null;
        if ($request->has('scenario')) {
            $scenarioId = $request->query('scenario');
            abort_unless(is_string($scenarioId), 404);
            $scenarioId = filter_var($scenarioId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999999999999999999]]);
            abort_if($scenarioId === false, 404);
            $selectedScenario = $analysis->scenarios()->findOrFail($scenarioId);
        }

        return view('analyses.show', [
            'analysis' => $analysis,
            'scenarios' => $analysis->scenarios()->latest('id')->paginate(5)->withQueryString(),
            'selectedScenario' => $selectedScenario,
        ]);
    }
}
