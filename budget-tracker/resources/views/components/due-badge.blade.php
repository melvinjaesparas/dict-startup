@props(['days'])
@if (is_null($days))
    <span class="badge-slate">No due date</span>
@elseif ($days < 0)
    <span class="badge-red">Overdue {{ abs($days) }}d</span>
@elseif ($days === 0)
    <span class="badge-red">Due today</span>
@elseif ($days <= 3)
    <span class="badge-amber">In {{ $days }}d</span>
@elseif ($days <= 7)
    <span class="badge-blue">In {{ $days }}d</span>
@else
    <span class="badge-slate">In {{ $days }}d</span>
@endif
