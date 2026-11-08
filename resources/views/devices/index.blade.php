@extends('layouts.app')
@section('title', 'My devices')
@section('content')
    <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="page-title">My devices</h1>
            <p class="mt-3 text-sm leading-6 text-text-muted">Open a device to review its details or start a comparison.</p>
        </div>
        <a href="{{ route('web.devices.create') }}" class="btn-primary shrink-0"><x-icon name="plus" class="size-4" />Add device</a>
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-x-4 border-b border-border">
        <nav class="flex flex-wrap gap-1.5" aria-label="Device status">
            @foreach(['active' => 'In use', 'archived' => 'Archived', 'all' => 'All devices'] as $value => $label)
                <a href="{{ route('web.devices.index', ['archived' => $value]) }}" class="filter-link" @if($filter === $value) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        <p class="py-2 text-sm tabular-nums text-text-muted">{{ $devices->total() }} {{ Str::plural('device', $devices->total()) }}</p>
    </div>

    @if($devices->isEmpty())
        <x-empty-state
            :icon="$filter === 'archived' ? 'archive' : 'devices'"
            :title="$filter === 'archived' ? 'No archived devices' : 'No devices in this view'"
            :description="$filter === 'archived' ? 'Archived devices and their saved comparisons will appear here.' : 'Add a device to compare repair and replacement costs.'" />
    @else
        <ul class="surface divide-y divide-border overflow-hidden">
            @foreach($devices as $device)<li><x-device-card :device="$device" /></li>@endforeach
        </ul>
        <x-pagination :paginator="$devices" />
    @endif
@endsection
