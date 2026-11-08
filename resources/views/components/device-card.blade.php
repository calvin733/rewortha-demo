@props(['device'])

<article class="flex min-w-0 flex-wrap items-center gap-x-1 pe-2">
    <a href="{{ route('web.devices.show', $device) }}" class="list-link grid-cols-[auto_minmax(0,1fr)] flex-1">
        <span class="flex size-9 shrink-0 items-center justify-center rounded-control bg-muted-surface text-primary">
            <x-icon :name="$device->category" />
        </span>
        <span class="min-w-0">
            <span class="block font-semibold [overflow-wrap:anywhere]">{{ $device->name }}</span>
            <span class="mt-0.5 block text-sm leading-5 text-text-muted">
                {{ ucfirst($device->category) }} · {{ $device->analyses_count }} {{ Str::plural('comparison', $device->analyses_count) }}
            </span>
        </span>
    </a>
    @if($device->archived_at)
        <span class="decision-badge my-2">Archived</span>
    @else
        <a href="{{ route('web.analyses.create', ['device_id' => $device->id]) }}" class="btn-ghost px-2.5">
            Compare<span class="sr-only"> {{ $device->name }}</span><x-icon name="chevron-right" class="hidden size-3.5 sm:block" />
        </a>
    @endif
</article>
