<?php

namespace App\Models;

use App\Models\Concerns\ImmutableSnapshot;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'input_snapshot', 'result_snapshot', 'calculation_version', 'classification'])]
class Scenario extends Model
{
    use ImmutableSnapshot;

    protected function casts(): array
    {
        return ['input_snapshot' => 'array', 'result_snapshot' => 'array'];
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }
}
