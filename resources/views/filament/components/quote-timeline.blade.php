@php
    $events = $getRecord()->timeline();

    // Literal class strings so Tailwind's scanner keeps them in the build.
    $dot = [
        'gray' => 'bg-gray-100 text-gray-500 ring-gray-950/10 dark:bg-white/10 dark:text-gray-400 dark:ring-white/10',
        'amber' => 'bg-amber-100 text-amber-600 ring-amber-500/20 dark:bg-amber-400/10 dark:text-amber-400',
        'blue' => 'bg-blue-100 text-blue-600 ring-blue-500/20 dark:bg-blue-400/10 dark:text-blue-400',
        'green' => 'bg-green-100 text-green-600 ring-green-500/20 dark:bg-green-400/10 dark:text-green-400',
        'red' => 'bg-red-100 text-red-600 ring-red-500/20 dark:bg-red-400/10 dark:text-red-400',
    ];
@endphp

<ol class="relative space-y-1">
    @forelse ($events as $event)
        <li class="relative flex gap-3 pb-4 last:pb-0">
            {{-- Connector, hidden on the final entry --}}
            @unless ($loop->last)
                <span class="absolute start-[15px] top-8 bottom-0 w-px bg-gray-200 dark:bg-white/10" aria-hidden="true"></span>
            @endunless

            <span @class([
                'relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full ring-1',
                $dot[$event['color']] ?? $dot['gray'],
            ])>
                <x-filament::icon :icon="$event['icon']" class="h-4 w-4" />
            </span>

            <div class="min-w-0 flex-1 pt-1">
                <div class="flex flex-wrap items-baseline justify-between gap-x-3">
                    <p class="text-sm font-medium text-gray-950 dark:text-white">
                        {{ $event['title'] }}
                        @if ($event['stage'] ?? null)
                            <span class="ms-1 rounded bg-gray-100 px-1.5 py-0.5 align-middle text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:bg-white/10 dark:text-gray-400">
                                at {{ $event['stage'] }}
                            </span>
                        @endif
                    </p>
                    <time
                        class="shrink-0 text-xs text-gray-400 dark:text-gray-500"
                        datetime="{{ $event['at']->toIso8601String() }}"
                        title="{{ $event['at']->format('D j M Y, H:i') }}"
                    >
                        {{ $event['at']->diffForHumans() }}
                    </time>
                </div>

                @if ($event['body'])
                    <p @class([
                        'mt-0.5 text-sm text-gray-500 dark:text-gray-400',
                        'whitespace-pre-line rounded-lg bg-gray-50 p-2.5 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10' => $event['type'] === 'note',
                    ])>{{ $event['body'] }}</p>
                @endif

                @if ($event['actor'])
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">by {{ $event['actor'] }}</p>
                @endif
            </div>
        </li>
    @empty
        <li class="text-sm text-gray-500 dark:text-gray-400">Nothing has happened yet.</li>
    @endforelse
</ol>
