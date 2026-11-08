<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListRequest;
use App\Http\Requests\Web\DeviceRequest;
use App\Models\Device;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeviceController extends Controller
{
    public function index(ListRequest $request): View
    {
        $filter = $request->validated('archived', 'active');
        $query = $request->user()->devices()->withCount('analyses');
        if ($filter === 'active') {
            $query->whereNull('archived_at');
        } elseif ($filter === 'archived') {
            $query->whereNotNull('archived_at');
        }

        return view('devices.index', [
            'devices' => $query->latest('id')->paginate($request->perPage())->withQueryString(),
            'filter' => $filter,
        ]);
    }

    public function create(): View
    {
        return view('devices.form', ['device' => new Device]);
    }

    public function store(DeviceRequest $request): RedirectResponse
    {
        $device = $request->user()->devices()->create($request->deviceData());

        return redirect()->route('web.devices.show', $device)->with('status', 'Device added. Ready to explore your options?');
    }

    public function show(Device $device): View
    {
        Gate::authorize('view', $device);

        return view('devices.show', [
            'device' => $device,
            'analyses' => $device->analyses()->withCount('scenarios')->latest('id')->paginate(10),
        ]);
    }

    public function edit(Device $device): View
    {
        Gate::authorize('update', $device);
        abort_if($device->archived_at !== null, 409, 'Archived devices cannot be edited.');

        return view('devices.form', ['device' => $device]);
    }

    public function update(DeviceRequest $request, Device $device): RedirectResponse
    {
        Gate::authorize('update', $device);
        DB::transaction(function () use ($request, $device): void {
            $lockedDevice = Device::query()->lockForUpdate()->findOrFail($device->id);
            abort_if($lockedDevice->archived_at !== null, 409, 'Archived devices cannot be edited.');
            $lockedDevice->update($request->deviceData());
        });

        return redirect()->route('web.devices.show', $device)->with('status', 'Device details updated. Saved analyses keep their original details.');
    }

    public function destroy(Device $device): RedirectResponse
    {
        Gate::authorize('delete', $device);
        DB::transaction(function () use ($device): void {
            $lockedDevice = Device::query()->lockForUpdate()->findOrFail($device->id);
            if ($lockedDevice->archived_at === null) {
                $lockedDevice->archived_at = now();
                $lockedDevice->save();
            }
        });

        return redirect()->route('web.devices.index')->with('status', 'Device archived. Your decision history is still available.');
    }
}
