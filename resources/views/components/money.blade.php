@props(['amount'])
<span {{ $attributes->merge(['class' => 'tabular-nums']) }}>Rp {{ number_format((int) $amount, 0, ',', '.') }}</span>
