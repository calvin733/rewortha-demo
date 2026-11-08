@extends('layouts.app')
@section('title', $device->name)
@section('content')
    <a href="{{ route('web.devices.index') }}" class="text-link mb-3 text-sm"><x-icon name="arrow-left" class="size-4" />My devices</a>

    <div class="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-end">
        <div class="min-w-0">
            <p class="eyebrow">{{ ucfirst($device->category) }} · {{ $device->archived_at ? 'Archived' : 'In use' }}</p>
            <h1 class="page-title mt-2">{{ $device->name }}</h1>
            @if($device->brand || $device->model)
                <p class="mt-2 text-sm text-text-muted [overflow-wrap:anywhere]">{{ trim(($device->brand ?? '').' '.($device->model ?? '')) }}</p>
            @endif
        </div>
        @unless($device->archived_at)
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('web.devices.edit', $device) }}" class="btn-secondary"><x-icon name="edit" class="size-4" />Edit device</a>
                <a href="{{ route('web.analyses.create', ['device_id' => $device->id]) }}" class="btn-primary"><x-icon name="compare" class="size-4" />New comparison</a>
            </div>
        @endunless
    </div>

    @if($device->archived_at)
        <p class="notice mb-6"><x-icon name="archive" class="mt-0.5 size-5 text-info" /><span>This device is archived. You can still open its saved comparisons.</span></p>
    @endif

    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_19rem]">
        <section class="min-w-0" aria-labelledby="device-comparisons">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h2 id="device-comparisons" class="section-title">Saved comparisons</h2>
                <p class="text-sm tabular-nums text-text-muted">{{ $analyses->total() }} saved</p>
            </div>
            @if($analyses->isEmpty())
                <x-empty-state icon="compare" title="No comparisons for this device" :description="$device->archived_at ? 'This device was archived before any comparisons were saved.' : 'Start a comparison with your repair and replacement estimates.'" />
            @else
                <div class="surface overflow-hidden"><x-history-list :analyses="$analyses" /></div>
                <x-pagination :paginator="$analyses" />
            @endif
        </section>

        <aside class="surface min-w-0 p-4 sm:p-5" aria-labelledby="device-details">
            <h2 id="device-details" class="section-title">Device details</h2>
            <dl class="mt-3 divide-y divide-border text-sm">
                <div class="flex justify-between gap-4 py-2.5"><dt class="text-text-muted">Category</dt><dd class="text-end font-medium">{{ ucfirst($device->category) }}</dd></div>
                <div class="flex justify-between gap-4 py-2.5"><dt class="text-text-muted">Purchase year</dt><dd class="text-end font-medium">{{ $device->purchase_year ?? 'Not added' }}</dd></div>
                <div class="flex justify-between gap-4 py-2.5"><dt class="text-text-muted">Added</dt><dd class="text-end font-medium"><time datetime="{{ $device->created_at->toDateString() }}">{{ $device->created_at->format('d M Y') }}</time></dd></div>
            </dl>
            <h3 class="mt-3 text-sm font-semibold">Condition notes</h3>
            <p class="mt-1 whitespace-pre-line text-sm leading-6 text-text-muted [overflow-wrap:anywhere]">{{ $device->condition_notes ?: 'No notes added.' }}</p>

            @unless($device->archived_at)
                <div class="mt-5 border-t border-border pt-4">
                    <p class="text-sm leading-6 text-text-muted">Archiving removes the device from your active list and keeps its comparison history.</p>
                    <form method="POST" action="{{ route('web.devices.destroy', $device) }}" data-submit-state data-confirm="Archive this device? Its comparisons will stay available." class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger" data-loading="Archiving…">
                            <x-icon name="archive" class="size-4" /><span data-button-label>Archive device</span>
                        </button>
                    </form>
                </div>
            @endunless
        </aside>
    </div>
@endsection
