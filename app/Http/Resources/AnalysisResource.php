<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'device_id' => $this->device_id, 'device_snapshot' => $this->device_snapshot,
            'input_snapshot' => $this->input_snapshot, 'result_snapshot' => $this->result_snapshot,
            'calculation_version' => $this->calculation_version, 'classification' => $this->classification,
            'scenarios_count' => $this->whenCounted('scenarios'), 'created_at' => $this->created_at,
        ];
    }
}
