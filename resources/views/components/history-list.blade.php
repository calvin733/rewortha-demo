@props(['analyses'])

<ul class="divide-y divide-border">
    @foreach($analyses as $analysis)
        <li>
            <a href="{{ route('web.analyses.show', $analysis) }}" class="list-link grid-cols-[minmax(0,1fr)_auto]">
                <span class="min-w-0">
                    <span class="block font-semibold [overflow-wrap:anywhere]">{{ $analysis->device_snapshot['name'] }}</span>
                    <span class="mt-1 block text-sm leading-5 text-text-muted">
                        <time datetime="{{ $analysis->created_at->toDateString() }}">{{ $analysis->created_at->format('d M Y') }}</time>
                        · Comparison #{{ $analysis->id }}
                    </span>
                    <span class="mt-2 inline-block sm:hidden"><x-decision-badge :classification="$analysis->classification" /></span>
                </span>
                <span class="flex items-center gap-3">
                    <span class="hidden sm:block"><x-decision-badge :classification="$analysis->classification" /></span>
                    <x-icon name="chevron-right" class="size-4 text-text-muted" />
                </span>
            </a>
        </li>
    @endforeach
</ul>
