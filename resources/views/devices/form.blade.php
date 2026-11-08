@extends('layouts.app')
@section('title', $device->exists ? 'Edit device' : 'Add device')
@section('content')
    @php
        $backUrl = $device->exists ? route('web.devices.show', $device) : route('web.devices.index');
    @endphp

    <div class="mx-auto max-w-3xl">
        <a href="{{ $backUrl }}" class="text-link mb-3 text-sm"><x-icon name="arrow-left" class="size-4" />Back to {{ $device->exists ? 'device' : 'devices' }}</a>
        <h1 class="page-title">{{ $device->exists ? 'Edit device' : 'Add a device' }}</h1>
        <p class="mb-6 mt-3 max-w-prose text-sm leading-6 text-text-muted">
            {{ $device->exists ? 'Update the device details. Saved comparisons keep the details recorded when they were created.' : 'Give the device a name and category so you can find its comparisons later.' }}
        </p>

        <x-validation-errors :fields="['name', 'category', 'purchase_year', 'brand', 'model', 'condition_notes']" />

        <form method="POST" action="{{ $device->exists ? route('web.devices.update', $device) : route('web.devices.store') }}" class="surface p-4 sm:p-6" data-submit-state>
            @csrf
            @if($device->exists) @method('PATCH') @endif

            <fieldset>
                <legend class="section-title">Device details</legend>
                <p class="mb-4 mt-2 text-sm text-text-muted">Fields marked * are required.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field name="name" label="Device name" :value="$device->name" placeholder="e.g. Study laptop" required maxlength="120" />
                    <x-field name="category" label="Category" type="select" required>
                        <option value="">Choose a category</option>
                        @foreach(['laptop' => 'Laptop', 'smartphone' => 'Smartphone', 'audio' => 'Audio', 'peripheral' => 'Peripheral', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('category', $device->category) === $value)>{{ $label }}</option>
                        @endforeach
                    </x-field>
                </div>
            </fieldset>

            <div class="mt-6 border-t border-border pt-5">
                <fieldset>
                    <legend class="section-title">Additional details <span class="ms-1 text-sm font-normal text-text-muted">Optional</span></legend>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <x-field name="brand" label="Brand" :value="$device->brand" maxlength="80" />
                        <x-field name="model" label="Model" :value="$device->model" maxlength="120" />
                        <x-field name="purchase_year" label="Purchase year" type="number" :value="$device->purchase_year" placeholder="e.g. 2022" min="1970" :max="now()->year" step="1" inputmode="numeric" />
                        <div class="sm:col-span-2">
                            <x-field name="condition_notes" label="Condition notes" :value="$device->condition_notes" textarea maxlength="2000" help="Record any issues or details you want to remember." />
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-2 border-t border-border pt-4">
                <a href="{{ $backUrl }}" class="btn-ghost">Cancel</a>
                <button type="submit" class="btn-primary">
                    <span data-button-label>{{ $device->exists ? 'Save changes' : 'Add device' }}</span><x-icon name="check" class="size-4" />
                </button>
            </div>
        </form>
    </div>
@endsection
