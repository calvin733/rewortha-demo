<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'name' => $this->name, 'category' => $this->category,
            'brand' => $this->brand, 'model' => $this->model, 'purchase_year' => $this->purchase_year,
            'condition_notes' => $this->condition_notes, 'archived_at' => $this->archived_at,
            'created_at' => $this->created_at, 'updated_at' => $this->updated_at,
        ];
    }
}
