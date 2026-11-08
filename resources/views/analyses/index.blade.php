@extends('layouts.app')
@section('title', 'Decision history')
@section('content')
    <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="page-title">Decision history</h1>
            <p class="mt-3 text-sm leading-6 text-text-muted">Review saved costs, outcomes, and what-if scenarios.</p>
        </div>
        <a href="{{ route('web.analyses.create') }}" class="btn-primary shrink-0"><x-icon name="plus" class="size-4" />New comparison</a>
    </div>

    <x-validation-errors :fields="['device_id']" />

    @if($devices->isNotEmpty())
        <form method="GET" action="{{ route('web.analyses.index') }}" class="mb-5 flex flex-wrap items-end gap-3" data-submit-state>
            <div class="w-full sm:w-80">
                <x-field name="device_id" label="Filter by device" type="select">
                    <option value="">All devices</option>
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}" @selected((string) request('device_id', '') === (string) $device->id)>
                            {{ $device->name }}{{ $device->archived_at ? ' (archived)' : '' }}
                        </option>
                    @endforeach
                </x-field>
            </div>
            <button type="submit" class="btn-secondary" data-loading="Filtering…"><span data-button-label>Apply filter</span></button>
            @if(request()->filled('device_id'))
                <a href="{{ route('web.analyses.index') }}" class="btn-ghost">Clear filter</a>
            @endif
        </form>
    @endif

    @if($analyses->isEmpty())
        <x-empty-state
            icon="history"
            :title="request()->filled('device_id') ? 'No comparisons for this device' : 'No saved comparisons yet'"
            :description="request()->filled('device_id') ? 'Choose another device or clear the filter to see all saved comparisons.' : 'Your first comparison will appear here with the inputs and result.'" />
    @else
        <section class="surface overflow-hidden" aria-labelledby="saved-comparisons">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-border px-4 py-3 sm:px-5">
                <h2 id="saved-comparisons" class="section-title">Saved comparisons</h2>
                <p class="text-sm tabular-nums text-text-muted">{{ $analyses->total() }} {{ Str::plural('comparison', $analyses->total()) }}</p>
            </div>
            <x-history-list :analyses="$analyses" />
        </section>
        <x-pagination :paginator="$analyses" />
    @endif
@endsection
