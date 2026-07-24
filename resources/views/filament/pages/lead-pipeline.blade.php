@php
    // Literal class strings so Tailwind's scanner can see them (interpolated
    // class names like bg-{{ $color }}-500 would be purged from the build).
    $dotClasses = [
        'new' => 'bg-zinc-400',
        'contacted' => 'bg-amber-400',
        'qualified' => 'bg-blue-400',
        'won' => 'bg-green-400',
        'lost' => 'bg-red-400',
    ];
@endphp

<x-filament-panels::page>
    <p class="-mt-2 text-sm text-gray-500 dark:text-gray-400">
        Drag a lead between columns to change its status. Response promise:
        {{ \App\Models\Quote::slaHours() }} hours.
    </p>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-5">
        @foreach ($this->getBoard() as $status => $column)
            <div
                wire:key="column-{{ $status }}"
                x-data="{ over: false }"
                x-on:dragover.prevent="over = true"
                x-on:dragleave="over = false"
                x-on:drop.prevent="over = false; $wire.moveQuote(Number($event.dataTransfer.getData('text/plain')), '{{ $status }}')"
                x-bind:class="over ? 'ring-2 ring-primary-500' : 'ring-1 ring-gray-950/5 dark:ring-white/10'"
                class="flex flex-col gap-3 rounded-xl bg-gray-50 p-3 transition dark:bg-white/5"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                        <span class="h-2 w-2 rounded-full {{ $dotClasses[$status] ?? 'bg-zinc-400' }}"></span>
                        {{ $column['label'] }}
                    </span>
                    <span class="rounded-md bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-300">
                        {{ $column['total'] }}
                    </span>
                </div>

                <div class="flex min-h-24 flex-col gap-2">
                    @forelse ($column['cards'] as $quote)
                        <div
                            wire:key="quote-{{ $quote->id }}"
                            draggable="true"
                            x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $quote->id }}'); $event.dataTransfer.effectAllowed = 'move'"
                            class="cursor-grab rounded-lg bg-white p-3 shadow-sm ring-1 ring-gray-950/5 transition active:cursor-grabbing hover:ring-primary-500 dark:bg-gray-900 dark:ring-white/10"
                        >
                            <a
                                href="{{ \App\Filament\Resources\QuoteResource::getUrl('view', ['record' => $quote]) }}"
                                class="block text-sm font-semibold text-gray-900 hover:text-primary-600 dark:text-white dark:hover:text-primary-400"
                            >
                                {{ $quote->name }}
                            </a>

                            @if ($quote->company)
                                <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ $quote->company }}</p>
                            @endif

                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                @if ($quote->budget)
                                    <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[11px] text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                        {{ $quote->budget }}
                                    </span>
                                @endif

                                @if ($quote->isOverdue())
                                    <span class="rounded bg-danger-100 px-1.5 py-0.5 text-[11px] font-medium text-danger-700 dark:bg-danger-400/10 dark:text-danger-400">
                                        Overdue
                                    </span>
                                @endif
                            </div>

                            <div class="mt-2 flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                                <span>{{ $quote->assignee?->name ?? 'Unassigned' }}</span>
                                <span title="{{ $quote->created_at?->format('D j M, H:i') }}">
                                    {{ $quote->created_at?->diffForHumans(short: true) }}
                                </span>
                            </div>

                            <div class="mt-2 flex items-center gap-3 border-t border-gray-100 pt-2 text-[11px] dark:border-white/10">
                                <button
                                    type="button"
                                    wire:click="mountAction('comment', { quote: {{ $quote->id }} })"
                                    class="font-medium text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400"
                                >
                                    Comment
                                </button>

                                @if ($quote->isClosed())
                                    <button
                                        type="button"
                                        wire:click="reopenQuote({{ $quote->id }})"
                                        class="font-medium text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400"
                                    >
                                        Reopen
                                    </button>
                                @endif

                                @if ($quote->notes_count)
                                    <span class="ms-auto text-gray-400 dark:text-gray-500">{{ $quote->notes_count }} note{{ $quote->notes_count === 1 ? '' : 's' }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-gray-400 dark:text-gray-500">Nothing here</p>
                    @endforelse

                    @if ($column['total'] > $column['cards']->count())
                        <p class="pt-1 text-center text-[11px] text-gray-400 dark:text-gray-500">
                            +{{ $column['total'] - $column['cards']->count() }} more
                        </p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
