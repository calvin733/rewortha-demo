<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Analysis;
use App\Models\Scenario;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $userId = $request->user()->id;
        $analyses = Analysis::query()->whereHas('device', fn ($query) => $query->where('user_id', $userId));

        return view('dashboard', [
            'deviceCount' => $request->user()->devices()->whereNull('archived_at')->count(),
            'analysisCount' => (clone $analyses)->count(),
            'scenarioCount' => Scenario::query()->whereHas('analysis.device', fn ($query) => $query->where('user_id', $userId))->count(),
            'devices' => $request->user()->devices()->whereNull('archived_at')->withCount('analyses')->latest('id')->limit(3)->get(),
            'recentAnalyses' => $analyses->withCount('scenarios')->latest('id')->limit(5)->get(),
        ]);
    }
}
