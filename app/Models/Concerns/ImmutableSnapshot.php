<?php

namespace App\Models\Concerns;

use LogicException;

trait ImmutableSnapshot
{
    protected static function bootImmutableSnapshot(): void
    {
        static::updating(function (): never {
            throw new LogicException('Historical snapshots cannot be updated.');
        });
        static::deleting(function (): never {
            throw new LogicException('Historical snapshots cannot be deleted through the model.');
        });
    }
}
