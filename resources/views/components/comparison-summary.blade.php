@props(['result', 'input'])

<div class="grid divide-y divide-border sm:grid-cols-2 sm:divide-x sm:divide-y-0">
    @foreach([['repair', 'Repair', 'wrench'], ['replacement', 'Replacement', 'devices']] as [$option, $label, $icon])
        <div class="min-w-0 px-4 py-5 sm:px-6">
            <p class="flex items-center gap-2 text-sm font-semibold">
                <x-icon :name="$icon" class="size-4 text-text-muted" />{{ $label }}
            </p>
            <p class="comparison-amount mt-2"><x-money :amount="$result[$option.'_monthly_cost']" /></p>
            <p class="mt-1 text-sm text-text-muted">Estimated per month</p>

            <dl class="mt-4 grid gap-y-2 border-t border-border pt-3 text-sm">
                <div class="flex flex-wrap justify-between gap-x-3">
                    <dt class="text-text-muted">Upfront cost</dt><dd class="font-medium"><x-money :amount="$input[$option.'_cost']" /></dd>
                </div>
                <div class="flex flex-wrap justify-between gap-x-3">
                    <dt class="text-text-muted">Expected use</dt><dd class="font-medium tabular-nums">{{ $input[$option.'_lifetime_months'] }} months</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-x-3">
                    <dt class="text-text-muted">Running cost per month</dt><dd class="font-medium"><x-money :amount="$input[$option.'_monthly_operating_cost']" /></dd>
                </div>
            </dl>
        </div>
    @endforeach
</div>

<div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 border-t border-border bg-muted-surface px-4 py-3 sm:px-6">
    <span class="text-sm font-medium text-text-muted">Estimated monthly difference</span>
    <x-money :amount="$result['monthly_difference']" class="text-lg font-semibold" />
</div>
