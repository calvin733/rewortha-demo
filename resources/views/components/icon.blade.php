@props(['name' => 'laptop'])
<svg {{ $attributes->merge(['class' => 'size-5 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('dashboard') <rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/> @break
        @case('devices') <rect x="3" y="4" width="13" height="12" rx="2"/><path d="M7 20h6m-3-4v4"/><rect x="17" y="9" width="4" height="11" rx="1"/> @break
        @case('history') <path d="M3 12a9 9 0 1 0 3-6.7L3 8m0-5v5h5"/><path d="M12 7v5l3 2"/> @break
        @case('plus') <path d="M12 5v14M5 12h14"/> @break
        @case('arrow-right') <path d="M4 12h16m-6-6 6 6-6 6"/> @break
        @case('arrow-left') <path d="M20 12H4m6-6-6 6 6 6"/> @break
        @case('chevron-right') <path d="m9 5 7 7-7 7"/> @break
        @case('compare') <path d="M4 7h15m-4-4 4 4-4 4M20 17H5m4-4-4 4 4 4"/> @break
        @case('wrench') <path d="M14 6a5 5 0 0 0-6 6l-5 5a2.8 2.8 0 0 0 4 4l5-5a5 5 0 0 0 6-6l-3 3-4-4 3-3Z"/> @break
        @case('spark') <path d="m12 3 2.4 6.6L21 12l-6.6 2.4L12 21l-2.4-6.6L3 12l6.6-2.4L12 3Z"/> @break
        @case('check') <path d="m5 12 4 4L19 6"/> @break
        @case('logout') <path d="M9 5H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4m6-14 6 7-6 7m-5-7h11"/> @break
        @case('archive') <rect x="3" y="3" width="18" height="5" rx="1"/><path d="M5 8v12h14V8M10 12h4"/> @break
        @case('edit') <path d="m14 5 5 5M4 20l5-1L21 7a2 2 0 0 0-5-5L4 15v5Z"/> @break
        @case('info') <circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10h.01"/> @break
        @case('smartphone') <rect x="6" y="2" width="12" height="20" rx="3"/><path d="M10 5h4m-3 14h2"/> @break
        @case('audio') <path d="M4 14v-3a8 8 0 0 1 16 0v3"/><rect x="3" y="12" width="4" height="8" rx="2"/><rect x="17" y="12" width="4" height="8" rx="2"/> @break
        @case('peripheral') <rect x="3" y="6" width="18" height="13" rx="2"/><path d="M7 10h.01M11 10h.01M15 10h2M7 14h10"/> @break
        @default <rect x="4" y="3" width="16" height="13" rx="2"/><path d="m4 16-2 5h20l-2-5M9 12h6"/>
    @endswitch
</svg>
