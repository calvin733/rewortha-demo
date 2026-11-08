@extends('layouts.app')
@section('title', 'Comparison result')
@section('content')
    @php
        $input = $analysis->input_snapshot;
        $outcomes = [
            'FAVOR_REPAIR' => ['Repair favored', 'Repair has the lower estimated monthly cost under these assumptions.'],
            'FAVOR_REPLACE' => ['Replace favored', 'Replacement has the lower estimated monthly cost under these assumptions.'],
            'CLOSE_CALL' => ['Close call', 'The monthly estimates are close. Cost alone does not clearly favor either option.'],
        ];
        [$outcome, $reason] = $outcomes[$analysis->classification];
    @endphp

    <a href="{{ route('web.analyses.index') }}" class="text-link mb-3 text-sm"><x-icon name="arrow-left" class="size-4" />Decision history</a>
    <div class="mb-5 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-end">
        <div class="min-w-0">
            <p class="eyebrow">Comparison #{{ $analysis->id }}</p>
            <h1 class="page-title mt-2">{{ $analysis->device_snapshot['name'] }}</h1>
            <p class="mt-2 text-sm text-text-muted">
                Saved <time datetime="{{ $analysis->created_at->toIso8601String() }}">{{ $analysis->created_at->format('d M Y, H:i') }} UTC</time>
            </p>
        </div>
        <a href="{{ route('web.devices.show', $analysis->device_id) }}" class="btn-secondary shrink-0">View device<x-icon name="arrow-right" class="size-4" /></a>
    </div>

    <section class="surface overflow-hidden" aria-labelledby="original-outcome">
        <div class="comparison-outcome">
            <p class="eyebrow">Original estimate</p>
            <h2 class="mt-1 text-2xl font-semibold tracking-tight" id="original-outcome">{{ $outcome }}</h2>
            <p class="mt-1 text-sm leading-6 text-foreground">{{ $reason }}</p>
        </div>
        <x-comparison-summary :result="$analysis->result_snapshot" :input="$input" />
    </section>

    @if($selectedScenario)
        <section class="surface mt-5 overflow-hidden" aria-labelledby="scenario-title">
            <div class="border-b border-border bg-primary-subtle px-4 py-4 sm:px-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="eyebrow">What-if scenario</p>
                        <h2 id="scenario-title" class="section-title mt-1 [overflow-wrap:anywhere]">{{ $selectedScenario->name ?: 'Scenario #'.$selectedScenario->id }}</h2>
                    </div>
                    <x-decision-badge :classification="$selectedScenario->classification" />
                </div>
                <p class="mt-2 text-sm leading-6">{{ $outcomes[$selectedScenario->classification][1] }}</p>
                <p class="mt-1 text-sm text-text-muted">Saved <time datetime="{{ $selectedScenario->created_at->toIso8601String() }}">{{ $selectedScenario->created_at->format('d M Y, H:i') }} UTC</time></p>
            </div>
            <x-comparison-summary :result="$selectedScenario->result_snapshot" :input="$selectedScenario->input_snapshot" />
            <div class="border-t border-border px-4 py-1 sm:px-6">
                <a href="{{ route('web.analyses.show', $analysis) }}" class="text-link text-sm">Return to the original only<x-icon name="arrow-right" class="size-4" /></a>
            </div>
        </section>
    @endif

    <details class="surface mt-4">
        <summary class="disclosure-toggle">
            <span class="flex items-center gap-2"><x-icon name="info" class="size-4 text-info" />About these estimates</span>
            <x-icon name="plus" class="size-4 text-primary" />
        </summary>
        <div class="space-y-2 border-t border-border px-4 py-4 text-sm leading-6 text-text-muted sm:px-5">
            <p>The result compares unrounded monthly estimates. The amounts shown are rounded.</p>
            <p>Costs and lifespans come from your assumptions. The monthly averages do not forecast total spending over the same period. Consider affordability, repeat failures, resale value, and performance separately.</p>
        </div>
    </details>

    <div class="mt-7 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_19rem]">
        <section class="min-w-0" aria-labelledby="what-if-title">
            <h2 id="what-if-title" class="section-title">What if things change?</h2>
            @if($analysis->device->archived_at)
                <p class="notice mt-3">This device is archived. You can still review saved scenarios.</p>
            @else
                <p class="mb-3 mt-2 text-sm leading-6 text-text-muted">Try different estimates while keeping the original comparison.</p>
                <details class="surface" id="what-if" @if($errors->any()) open @endif>
                    <summary class="disclosure-toggle bg-primary-subtle">
                        <span class="flex items-center gap-2"><x-icon name="compare" class="size-4 text-primary" />Try a what-if scenario</span>
                        <x-icon name="plus" class="size-4 text-primary" />
                    </summary>
                    <div class="border-t border-border p-4 sm:p-5">
                        <x-validation-errors :fields="['name', 'repair_cost', 'repair_lifetime_months', 'replacement_cost', 'replacement_lifetime_months', 'repair_monthly_operating_cost', 'replacement_monthly_operating_cost']" />
                        <p class="mb-4 text-sm leading-6 text-text-muted">Change at least one estimate. Blank fields keep the original values shown in the form.</p>
                        <form method="POST" action="{{ route('web.scenarios.store', $analysis) }}" class="space-y-4" data-submit-state>
                            @csrf
                            <x-field name="name" label="Scenario name (optional)" placeholder="e.g. Shorter repair life" maxlength="120" />
                            <div class="grid gap-4 sm:grid-cols-2">
                                <fieldset class="space-y-3">
                                    <legend class="mb-2 font-semibold">Repair</legend>
                                    <x-field name="repair_cost" label="Repair cost (Rp)" type="number" min="0" max="1000000000" step="1" inputmode="numeric" :placeholder="$input['repair_cost']" />
                                    <x-field name="repair_lifetime_months" label="Use after repair (months)" type="number" min="1" max="240" step="1" inputmode="numeric" :placeholder="$input['repair_lifetime_months']" />
                                </fieldset>
                                <fieldset class="space-y-3">
                                    <legend class="mb-2 font-semibold">Replacement</legend>
                                    <x-field name="replacement_cost" label="Replacement price (Rp)" type="number" min="0" max="1000000000" step="1" inputmode="numeric" :placeholder="$input['replacement_cost']" />
                                    <x-field name="replacement_lifetime_months" label="Use of replacement (months)" type="number" min="1" max="240" step="1" inputmode="numeric" :placeholder="$input['replacement_lifetime_months']" />
                                </fieldset>
                            </div>

                            <details class="rounded-control border border-border" @if($errors->hasAny(['repair_monthly_operating_cost', 'replacement_monthly_operating_cost']) || filled(old('repair_monthly_operating_cost')) || filled(old('replacement_monthly_operating_cost'))) open @endif>
                                <summary class="disclosure-toggle">
                                    <span>Change monthly running costs</span><x-icon name="plus" class="size-4 text-primary" />
                                </summary>
                                <div class="grid gap-4 border-t border-border p-4 sm:grid-cols-2">
                                    <x-field name="repair_monthly_operating_cost" label="Repaired device, per month (Rp)" type="number" min="0" max="10000000" step="1" inputmode="numeric" :placeholder="$input['repair_monthly_operating_cost']" />
                                    <x-field name="replacement_monthly_operating_cost" label="Replacement, per month (Rp)" type="number" min="0" max="10000000" step="1" inputmode="numeric" :placeholder="$input['replacement_monthly_operating_cost']" />
                                </div>
                            </details>

                            <div class="flex justify-end border-t border-border pt-4">
                                <button type="submit" class="btn-primary" data-loading="Comparing scenario…">
                                    <span data-button-label>Compare and save scenario</span><x-icon name="arrow-right" class="size-4" />
                                </button>
                            </div>
                        </form>
                    </div>
                </details>
            @endif
        </section>

        <section class="min-w-0" aria-labelledby="saved-scenarios">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h2 id="saved-scenarios" class="section-title">Saved scenarios</h2>
                <p class="text-sm tabular-nums text-text-muted">{{ $scenarios->total() }} saved</p>
            </div>
            @if($scenarios->isEmpty())
                <x-empty-state icon="compare" title="No scenarios yet" description="Saved alternatives will appear here." />
            @else
                <ul class="surface divide-y divide-border overflow-hidden">
                    @foreach($scenarios as $scenario)
                        <li>
                            <a href="{{ route('web.analyses.show', ['analysis' => $analysis, 'scenario' => $scenario->id]) }}"
                                @class(['list-link grid-cols-[minmax(0,1fr)_auto]', 'bg-primary-subtle' => $selectedScenario?->id === $scenario->id])
                                @if($selectedScenario?->id === $scenario->id) aria-current="page" @endif>
                                <span class="min-w-0">
                                    <span class="block font-semibold [overflow-wrap:anywhere]">{{ $scenario->name ?: 'Scenario #'.$scenario->id }}</span>
                                    <span class="mt-1 block text-sm leading-5 text-text-muted">
                                        {{ $outcomes[$scenario->classification][0] }} · <time datetime="{{ $scenario->created_at->toDateString() }}">{{ $scenario->created_at->format('d M Y') }}</time>
                                    </span>
                                </span>
                                <x-icon name="chevron-right" class="size-4 text-text-muted" />
                            </a>
                        </li>
                    @endforeach
                </ul>
                <x-pagination :paginator="$scenarios" />
            @endif
        </section>
    </div>
@endsection
