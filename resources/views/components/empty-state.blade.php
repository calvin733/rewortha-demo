@props(['icon' => 'devices', 'title', 'description'])

<div {{ $attributes->merge(['class' => 'surface flex items-start gap-4 px-4 py-6 sm:px-5']) }}>
    <span class="flex size-10 shrink-0 items-center justify-center rounded-control bg-muted-surface text-primary">
        <x-icon :name="$icon" />
    </span>
    <div class="min-w-0">
        <p class="font-semibold">{{ $title }}</p>
        <p class="mt-1 max-w-prose text-sm leading-6 text-text-muted">{{ $description }}</p>
        @if($slot->isNotEmpty())<div class="mt-3">{{ $slot }}</div>@endif
    </div>
</div>
