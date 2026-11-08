<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate(['check' => ['sometimes', 'in:database']]);
        $data = ['status' => 'ok', 'service' => 'ReWortha', 'version' => '1.0.0'];
        if ($request->query('check') === 'database') {
            try {
                DB::select('select 1');
                $data['database'] = 'ok';
            } catch (Throwable) {
                Log::warning('Database readiness check failed.');

                return response()->json(['data' => ['status' => 'unavailable', 'service' => 'ReWortha', 'database' => 'unavailable']], 503);
            }
        }

        return response()->json(['data' => $data]);
    }
}
