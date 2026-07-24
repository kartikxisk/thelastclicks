<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory, LogsActivity;

    public const STATUSES = ['new', 'contacted', 'qualified', 'won', 'lost'];

    /** Still in play — everything that is neither won nor lost. */
    public const OPEN_STATUSES = ['new', 'contacted', 'qualified'];

    public const CLOSED_STATUSES = ['won', 'lost'];

    /**
     * The pipeline only ever runs forwards: new → contacted → qualified → won.
     * A lead can be lost at any open stage, and nothing may return to `new` —
     * a closed lead comes back through reopen() instead, which restores the
     * stage it was at when it closed.
     */
    public const TRANSITIONS = [
        'new' => ['contacted', 'lost'],
        'contacted' => ['qualified', 'lost'],
        'qualified' => ['won', 'lost'],
        'won' => [],
        'lost' => [],
    ];

    /** Default response promise, matching the "within 4 working hours" site copy. */
    public const DEFAULT_SLA_HOURS = 4;

    protected $fillable = [
        'name', 'company', 'email', 'phone', 'project_type', 'budget', 'timeline',
        'message', 'source_page', 'ip', 'ua', 'status', 'assigned_to',
        'contacted_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'contacted_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'assigned_to'])
            ->logOnlyDirty();
    }

    /** @return HasMany<QuoteNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(QuoteNote::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Response-time promise in hours.
     *
     * Memoised on the container rather than a static so it costs one query per
     * request while staying isolated between tests (each test boots a fresh app).
     */
    public static function slaHours(): int
    {
        if (! app()->bound('lead.sla_hours')) {
            app()->instance('lead.sla_hours', max(1, (int) SiteSetting::get('lead_sla_hours', self::DEFAULT_SLA_HOURS)));
        }

        return app('lead.sla_hours');
    }

    /** When this lead should have been responded to. */
    public function slaDueAt(): ?Carbon
    {
        return $this->created_at?->copy()->addHours(self::slaHours());
    }

    /** Still sitting in `new` past the response promise. */
    public function isOverdue(): bool
    {
        if ($this->status !== 'new') {
            return false;
        }

        $due = $this->slaDueAt();

        return $due !== null && $due->isPast();
    }

    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED_STATUSES, true);
    }

    /** Stages this lead may legally move to right now. */
    public function allowedTransitions(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    /**
     * Move the lead one step along the pipeline, optionally recording why.
     * Returns false when the move breaks the hierarchy, so callers can report
     * it rather than silently writing an illegal stage.
     */
    public function transitionTo(string $status, ?string $comment = null, ?User $actor = null): bool
    {
        if (! $this->canTransitionTo($status)) {
            return false;
        }

        // LogsActivity records the status change itself; the note carries the why.
        $this->status = $status;
        $this->save();

        if (filled($comment)) {
            $this->comment($comment, $actor, $status);
        }

        return true;
    }

    /**
     * Bring a closed lead back to the stage it was at when it closed, so a
     * reopened deal resumes where it left off instead of restarting.
     */
    public function reopen(?string $comment = null, ?User $actor = null): bool
    {
        if (! $this->isClosed()) {
            return false;
        }

        $this->status = $this->stageBeforeClosing();
        $this->closed_at = null;
        $this->save();

        if (filled($comment)) {
            $this->comment($comment, $actor, $this->status);
        }

        return true;
    }

    /** The open stage this lead sat in immediately before it was won or lost. */
    protected function stageBeforeClosing(): string
    {
        $lastClose = $this->activities()
            ->where('event', 'updated')
            ->latest('id')
            ->get()
            ->first(function ($activity): bool {
                $to = $activity->changes()['attributes']['status'] ?? null;

                return in_array($to, self::CLOSED_STATUSES, true);
            });

        $previous = $lastClose?->changes()['old']['status'] ?? null;

        // Never resurrect a lead into `new` — it has demonstrably been worked.
        return in_array($previous, ['contacted', 'qualified'], true) ? $previous : 'contacted';
    }

    /** Attach a note, tagged with the stage the lead was at when it was written. */
    public function comment(string $body, ?User $actor = null, ?string $stage = null): QuoteNote
    {
        return $this->notes()->create([
            'author_id' => $actor?->id ?? auth()->id(),
            'body' => $body,
            'stage' => $stage ?? $this->status,
        ]);
    }

    /** Minutes from arrival to first response, or null when never contacted. */
    public function responseMinutes(): ?int
    {
        if (! $this->contacted_at || ! $this->created_at) {
            return null;
        }

        return (int) $this->created_at->diffInMinutes($this->contacted_at);
    }

    /**
     * CRM-style event stream for this lead: the enquiry itself, every logged
     * status / owner change, and every note, newest first.
     *
     * @return Collection<int, array{type: string, icon: string, color: string, title: string, body: ?string, actor: ?string, at: Carbon}>
     */
    public function timeline(): Collection
    {
        $this->loadMissing(['activities.causer', 'notes.author']);

        $names = User::query()->pluck('name', 'id');
        $events = collect();

        foreach ($this->activities as $activity) {
            // Creation is already told by the "Enquiry received" event below; its
            // logged attributes would otherwise surface as "New → New".
            if ($activity->event !== 'updated') {
                continue;
            }

            $changes = $activity->changes();
            $after = $changes['attributes'] ?? [];
            $before = $changes['old'] ?? [];
            $actor = $activity->causer?->name;

            if (array_key_exists('status', $after) && ($before['status'] ?? null) !== $after['status']) {
                $events->push($this->statusEvent(
                    $before['status'] ?? null,
                    (string) $after['status'],
                    $activity->created_at,
                    $actor,
                    (int) $activity->id,
                ));
            }

            if (array_key_exists('assigned_to', $after) && ($before['assigned_to'] ?? null) !== $after['assigned_to']) {
                $to = $after['assigned_to'];
                $events->push([
                    'type' => 'owner',
                    'icon' => $to === null ? 'heroicon-m-user-minus' : 'heroicon-m-user-plus',
                    'color' => 'gray',
                    'title' => $to === null ? 'Owner removed' : 'Assigned to '.($names[$to] ?? 'someone'),
                    'body' => null,
                    'actor' => $actor,
                    'at' => $activity->created_at,
                    'seq' => (int) $activity->id,
                ]);
            }
        }

        foreach ($this->notes as $note) {
            $events->push([
                'type' => 'note',
                'icon' => 'heroicon-m-chat-bubble-left-ellipsis',
                'color' => 'blue',
                'title' => 'Note added',
                'body' => $note->body,
                'actor' => $note->author?->name,
                'at' => $note->created_at,
                'seq' => (int) $note->id,
                'stage' => $note->stage,
            ]);
        }

        if ($this->created_at) {
            $events->push([
                'type' => 'received',
                'icon' => 'heroicon-m-inbox-arrow-down',
                'color' => 'gray',
                'title' => 'Enquiry received',
                'body' => collect([$this->project_type, $this->budget, $this->source_page])->filter()->implode(' · ') ?: null,
                'actor' => null,
                'at' => $this->created_at,
                'seq' => 0,
            ]);
        }

        // Newest first; `seq` breaks ties when several events land in the same second.
        return $events->sortBy([['at', 'desc'], ['seq', 'desc']])->values();
    }

    /**
     * A status change. Moving off `new` is the first response, so it is labelled
     * with how long the lead actually waited.
     *
     * @return array{type: string, icon: string, color: string, title: string, body: ?string, actor: ?string, at: Carbon}
     */
    protected function statusEvent(?string $from, string $to, Carbon $at, ?string $actor, int $seq): array
    {
        $responded = $from === 'new' && $this->created_at !== null;

        return [
            'type' => 'status',
            'icon' => match ($to) {
                'won' => 'heroicon-m-trophy',
                'lost' => 'heroicon-m-x-circle',
                'qualified' => 'heroicon-m-check-badge',
                default => 'heroicon-m-arrow-right-circle',
            },
            'color' => match ($to) {
                'won' => 'green',
                'lost' => 'red',
                'qualified' => 'blue',
                'contacted' => 'amber',
                default => 'gray',
            },
            'title' => $responded && $to === 'contacted'
                ? 'Responded'
                : ucfirst($from ?? 'new').' → '.ucfirst($to),
            'body' => $responded
                ? 'Waited '.$this->created_at->diffForHumans($at, ['syntax' => CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2])
                : null,
            'actor' => $actor,
            'at' => $at,
            'seq' => $seq,
        ];
    }

    /**
     * Sales users only ever see the leads assigned to them; every other role
     * sees the full board. Single source of truth for the resource table, the
     * dashboard widgets and the pipeline.
     *
     * @param  Builder<Quote>  $q
     */
    public function scopeVisibleTo(Builder $q, ?User $user): void
    {
        if ($user && $user->hasRole('Sales') && ! $user->hasRole('Super-admin')) {
            $q->where('assigned_to', $user->id);
        }
    }

    /** @param Builder<Quote> $q */
    public function scopeOpen(Builder $q): void
    {
        $q->whereIn('status', self::OPEN_STATUSES);
    }

    /** Arrived but nobody has responded yet. @param Builder<Quote> $q */
    public function scopeUnactioned(Builder $q): void
    {
        $q->where('status', 'new');
    }

    /** Unactioned past the response promise. @param Builder<Quote> $q */
    public function scopeOverdue(Builder $q): void
    {
        $q->where('status', 'new')
            ->where('created_at', '<', now()->subHours(self::slaHours()));
    }
}
