<?php

namespace App\Filament\Pages;

use App\Models\Quote;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

/**
 * Drag-and-drop lead board. Access is governed by the Shield permission
 * `page_LeadPipeline`; the drop handler additionally re-checks QuotePolicy
 * server-side, so hiding a card in the UI is never the only thing standing
 * between a user and a status change.
 */
class LeadPipeline extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static ?string $navigationGroup = 'Leads';

    protected static ?string $navigationLabel = 'Pipeline';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Lead pipeline';

    protected static ?string $slug = 'lead-pipeline';

    protected static string $view = 'filament.pages.lead-pipeline';

    /** Cards rendered per column before the "+N more" hint takes over. */
    protected const CARDS_PER_COLUMN = 50;

    public static function getNavigationBadge(): ?string
    {
        $overdue = Quote::query()->visibleTo(auth()->user())->overdue()->count();

        return $overdue > 0 ? (string) $overdue : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    /**
     * One column per status: the visible cards plus the true total.
     *
     * @return array<string, array{label: string, color: string, total: int, cards: Collection<int, Quote>}>
     */
    public function getBoard(): array
    {
        $user = auth()->user();

        $totals = Quote::query()
            ->visibleTo($user)
            ->get(['status'])
            ->countBy('status');

        $board = [];

        foreach (Quote::STATUSES as $status) {
            $board[$status] = [
                'label' => ucfirst($status),
                'color' => $this->columnColor($status),
                'total' => (int) ($totals[$status] ?? 0),
                'cards' => Quote::query()
                    ->visibleTo($user)
                    ->where('status', $status)
                    ->with('assignee')
                    ->withCount('notes')
                    ->latest()
                    ->limit(self::CARDS_PER_COLUMN)
                    ->get(),
            ];
        }

        return $board;
    }

    /** Drop handler. Called from the board via $wire. */
    public function moveQuote(int $quoteId, string $status): void
    {
        if (! in_array($status, Quote::STATUSES, true)) {
            return;
        }

        $quote = $this->authorisedQuote($quoteId);

        if (! $quote || $quote->status === $status) {
            return;
        }

        $from = $quote->status;

        // The pipeline only runs forwards; a closed lead comes back via Reopen.
        if (! $quote->transitionTo($status, actor: auth()->user())) {
            Notification::make()
                ->title("Can't move ".ucfirst($from).' → '.ucfirst($status))
                ->body($quote->isClosed()
                    ? 'This lead is closed. Use Reopen to put it back in play.'
                    : 'Leads follow new → contacted → qualified → won, and can be lost at any stage.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title($quote->name.': '.$from.' → '.$status)
            ->success()
            ->send();
    }

    /** Put a won/lost lead back into the stage it closed from. */
    public function reopenQuote(int $quoteId): void
    {
        $quote = $this->authorisedQuote($quoteId);

        if (! $quote || ! $quote->reopen(actor: auth()->user())) {
            return;
        }

        Notification::make()
            ->title($quote->name.' reopened at '.$quote->status)
            ->success()
            ->send();
    }

    /**
     * Resolve a lead the current user is allowed to change, or null. Both the
     * visibility scope and QuotePolicy are re-checked server-side.
     */
    protected function authorisedQuote(int $quoteId): ?Quote
    {
        $quote = Quote::query()->visibleTo(auth()->user())->find($quoteId);

        if (! $quote) {
            return null;
        }

        if (Gate::denies('update', $quote)) {
            Notification::make()
                ->title('Not allowed')
                ->body('You cannot change this lead.')
                ->danger()
                ->send();

            return null;
        }

        return $quote;
    }

    /** Add a note against a lead, tagged with the stage it is sitting at. */
    public function commentAction(): Action
    {
        return Action::make('comment')
            ->label('Add comment')
            ->icon('heroicon-m-chat-bubble-left-ellipsis')
            ->modalHeading('Add a comment')
            ->form([
                Textarea::make('body')->label('Comment')->required()->rows(4),
            ])
            ->action(function (array $arguments, array $data): void {
                $quote = $this->authorisedQuote((int) ($arguments['quote'] ?? 0));

                if (! $quote) {
                    return;
                }

                $quote->comment($data['body'], auth()->user());

                Notification::make()->title('Comment added')->success()->send();
            });
    }

    protected function columnColor(string $status): string
    {
        return match ($status) {
            'new' => 'zinc',
            'contacted' => 'amber',
            'qualified' => 'blue',
            'won' => 'green',
            'lost' => 'red',
            default => 'zinc',
        };
    }
}
