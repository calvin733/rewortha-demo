<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScenarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'analysis_id' => $this->analysis_id, 'name' => $this->name,
            'input_snapshot' => $this->input_snapshot, 'result_snapshot' => $this->result_snapshot,
            'calculation_version' => $this->calculation_version, 'classification' => $this->classification,
            'baseline' => new AnalysisResource($this->whenLoaded('analysis')), 'created_at' => $this->created_at,
        ];
    }
}
