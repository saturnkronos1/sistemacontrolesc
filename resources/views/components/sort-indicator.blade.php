@props(['field', 'sortField', 'sortDirection'])

@if($sortField === $field)
    <svg class="size-3.5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
        @if($sortDirection === 'asc')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/>
        @else
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
        @endif
    </svg>
@else
    <svg class="size-3.5 text-sky-300/50" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15.75L12 19.5l3.75-3.75"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 8.25L12 4.5l3.75 3.75"/>
    </svg>
@endif
