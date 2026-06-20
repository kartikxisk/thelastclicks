@php($logs = $getRecord()->activities()->latest()->take(20)->get())
<ul class="space-y-2">
    @forelse ($logs as $log)
        <li class="text-sm">
            <span class="text-gray-400">{{ $log->created_at->diffForHumans() }}</span> —
            <strong>{{ $log->description }}</strong>
            @if ($log->causer) by {{ $log->causer->name }} @endif
        </li>
    @empty
        <li class="text-sm text-gray-500">No activity yet.</li>
    @endforelse
</ul>
