<?php

namespace App\Models;

use App\Models\Concerns\ImmutableSnapshot;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['device_snapshot', 'input_snapshot', 'result_snapshot', 'calculation_version', 'classification'])]
class Analysis extends Model
{
    use ImmutableSnapshot;

    protected function casts(): array
    {
        return ['device_snapshot' => 'array', 'input_snapshot' => 'array', 'result_snapshot' => 'array'];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function scenarios(): HasMany
    {
        return $this->hasMany(Scenario::class);
    }
}
