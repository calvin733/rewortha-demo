@extends('layouts.app')
@section('title', 'New comparison')
@section('content')
    <div class="mx-auto max-w-4xl">
        <a href="{{ route('web.devices.index') }}" class="text-link mb-3 text-sm"><x-icon name="arrow-left" class="size-4" />My devices</a>
        <p class="eyebrow">New comparison</p>
        <h1 class="page-title mt-2">Repair or replace?</h1>
        <p class="mb-6 mt-3 max-w-prose text-sm leading-6 text-text-muted">
            Enter the cost and expected lifespan for both options. Use whole rupiah without separators. Fields marked * are required.
        </p>

        <x-validation-errors :fields="['device_id', 'repair_cost', 'repair_lifetime_months', 'replacement_cost', 'replacement_lifetime_months', 'repair_monthly_operating_cost', 'replacement_monthly_operating_cost']" />

        <form method="POST" action="{{ route('web.analyses.store') }}" class="space-y-4" data-submit-state>
            @csrf
            <div class="surface px-4 py-4 sm:px-5">
                <x-field name="device_id" label="Device to compare" type="select" required>
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}" @selected($selectedDeviceId === (string) $device->id)>{{ $device->name }} · {{ ucfirst($device->category) }}</option>
                    @endforeach
                </x-field>
            </div>

            <div class="surface grid divide-y divide-border sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                <div class="min-w-0 p-4 sm:p-5">
                    <fieldset>
                        <legend class="section-title">
                            <span class="inline-flex items-center gap-2"><x-icon name="wrench" class="size-5 text-primary" />Repair</span>
                        </legend>
                        <div class="mt-3 space-y-4">
                            <x-field name="repair_cost" label="Estimated repair cost (Rp)" type="number" min="0" max="1000000000" step="1" inputmode="numeric" required />
                            <x-field name="repair_lifetime_months" label="Expected use after repair (months)" type="number" min="1" max="240" step="1" inputmode="numeric" required />
                        </div>
                    </fieldset>
                </div>
                <div class="min-w-0 p-4 sm:p-5">
                    <fieldset>
                        <legend class="section-title">
                            <span class="inline-flex items-center gap-2"><x-icon name="devices" class="size-5 text-primary" />Replacement</span>
                        </legend>
                        <div class="mt-3 space-y-4">
                            <x-field name="replacement_cost" label="Replacement price (Rp)" type="number" min="0" max="1000000000" step="1" inputmode="numeric" required />
                            <x-field name="replacement_lifetime_months" label="Expected use of replacement (months)" type="number" min="1" max="240" step="1" inputmode="numeric" required />
                        </div>
                    </fieldset>
                </div>
            </div>

            <details class="surface" @if($errors->hasAny(['repair_monthly_operating_cost', 'replacement_monthly_operating_cost']) || filled(old('repair_monthly_operating_cost')) || filled(old('replacement_monthly_operating_cost'))) open @endif>
                <summary class="disclosure-toggle">
                    <span>Monthly running costs <span class="ms-1 font-normal text-text-muted">Optional</span></span>
                    <x-icon name="plus" class="size-4 text-primary" />
                </summary>
                <div class="border-t border-border px-4 pb-4 pt-3 sm:px-5 sm:pb-5">
                    <p class="mb-4 text-sm leading-6 text-text-muted">Include regular costs such as electricity. Blank amounts count as Rp 0.</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-field name="repair_monthly_operating_cost" label="Repaired device, per month (Rp)" type="number" min="0" max="10000000" step="1" inputmode="numeric" placeholder="0" />
                        <x-field name="replacement_monthly_operating_cost" label="Replacement, per month (Rp)" type="number" min="0" max="10000000" step="1" inputmode="numeric" placeholder="0" />
                    </div>
                </div>
            </details>

            <div class="flex flex-col justify-between gap-3 border-t border-border pt-4 sm:flex-row sm:items-center">
                <p class="max-w-sm text-sm leading-6 text-text-muted">Your estimates and the result will be saved to Decision history.</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <a href="{{ route('web.devices.index') }}" class="btn-ghost">Cancel</a>
                    <button type="submit" class="btn-primary" data-loading="Comparing…">
                        <span data-button-label>Compare and save</span><x-icon name="arrow-right" class="size-4" />
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
