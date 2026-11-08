@props(['classification'])

<span class="decision-badge">
    {{ match ($classification) {
        'FAVOR_REPAIR' => 'Repair favored',
        'FAVOR_REPLACE' => 'Replace favored',
        default => 'Close call',
    } }}
</span>
