<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeviceRequest;
use App\Http\Requests\ListRequest;
use App\Http\Resources\DeviceResource;
use App\Models\Device;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeviceController extends Controller
{
    public function index(ListRequest $request): AnonymousResourceCollection
    {
        $query = $request->user()->devices();
        match ($request->validated('archived', 'active')) {
            'active' => $query->whereNull('archived_at'),
            'archived' => $query->whereNotNull('archived_at'),
            default => $query,
        };

        return DeviceResource::collection($query->latest('id')->paginate($request->perPage())->withQueryString());
    }

    public function store(DeviceRequest $request): DeviceResource
    {
        return new DeviceResource($request->user()->devices()->create($request->validated()));
    }

    public function show(Device $device): DeviceResource
    {
        Gate::authorize('view', $device);

        return new DeviceResource($device);
    }

    public function update(DeviceRequest $request, Device $device): DeviceResource
    {
        Gate::authorize('update', $device);

        return DB::transaction(function () use ($request, $device): DeviceResource {
            $device = Device::query()->lockForUpdate()->findOrFail($device->id);
            abort_if($device->archived_at !== null, 409, 'Archived devices cannot be edited.');
            $device->update($request->validated());

            return new DeviceResource($device);
        });
    }

    public function destroy(Device $device): Response
    {
        Gate::authorize('delete', $device);
        DB::transaction(function () use ($device): void {
            $device = Device::query()->lockForUpdate()->findOrFail($device->id);
            if ($device->archived_at === null) {
                $device->archived_at = now();
                $device->save();
            }
        });

        return response()->noContent();
    }
}
