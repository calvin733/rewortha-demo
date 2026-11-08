@extends('layouts.app')
@section('title', 'Overview')
@section('content')
    <div class="mb-6">
        <p class="eyebrow">Overview</p>
        <h1 class="page-title mt-2">Hello, {{ explode(' ', auth('web')->user()->name)[0] }}.</h1>
    </div>

    <section class="comparison-outcome rounded-e-panel py-5 sm:py-6" aria-labelledby="start-comparison">
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div class="min-w-0">
                <h2 class="section-title" id="start-comparison">Repair or replace?</h2>
                <p class="mt-2 max-w-prose text-sm leading-6 text-text-muted">
                    {{ $deviceCount ? 'Choose a device and enter your cost and lifespan estimates to compare both options.' : 'Add your first device to compare the estimated monthly costs of repairing and replacing it.' }}
                </p>
            </div>
            <a href="{{ route($deviceCount ? 'web.analyses.create' : 'web.devices.create') }}" class="btn-primary shrink-0">
                <x-icon :name="$deviceCount ? 'compare' : 'plus'" class="size-4" />
                {{ $deviceCount ? 'New comparison' : 'Add device' }}
            </a>
        </div>
    </section>

    <div class="mt-8 grid items-start gap-7 lg:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)]">
        <section aria-labelledby="recent-title" class="min-w-0">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-x-4">
                <h2 class="section-title" id="recent-title">Recent comparisons</h2>
                @if($analysisCount)
                    <a href="{{ route('web.analyses.index') }}" class="text-link text-sm">View history<x-icon name="arrow-right" class="size-4" /></a>
                @endif
            </div>
            @if($recentAnalyses->isEmpty())
                <x-empty-state icon="history" title="No comparisons yet" description="After you save a comparison, you can open it here to review the costs and result." />
            @else
                <div class="surface overflow-hidden"><x-history-list :analyses="$recentAnalyses" /></div>
            @endif
        </section>

        <section aria-labelledby="devices-title" class="min-w-0">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-x-4">
                <h2 class="section-title" id="devices-title">Devices <span class="ms-1 text-sm font-normal text-text-muted">{{ $deviceCount }} in use</span></h2>
                @if($deviceCount)
                    <a href="{{ route('web.devices.index') }}" class="text-link text-sm">View devices<x-icon name="arrow-right" class="size-4" /></a>
                @endif
            </div>
            @if($devices->isEmpty())
                <x-empty-state title="Every device has a story." description="A name and category are enough to add your first device." />
            @else
                <ul class="surface divide-y divide-border overflow-hidden">
                    @foreach($devices as $device)<li><x-device-card :device="$device" /></li>@endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
