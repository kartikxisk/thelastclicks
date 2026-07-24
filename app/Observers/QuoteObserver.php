<?php

namespace App\Observers;

use App\Models\Quote;

/**
 * Maintains the lead lifecycle timestamps so the dashboard can measure response
 * time and conversion without replaying the activity log.
 */
class QuoteObserver
{
    public function saving(Quote $quote): void
    {
        // Only react to an actual status change (covers creation too, where the
        // original is null and a lead may be seeded straight into a later stage).
        if (! $quote->isDirty('status')) {
            return;
        }

        $status = $quote->status;

        // First move off `new` is the moment someone responded. Never overwrite:
        // a lead bounced back to `new` keeps its original response time.
        if ($status !== 'new' && $quote->contacted_at === null) {
            $quote->contacted_at = now();
        }

        if (in_array($status, ['won', 'lost'], true)) {
            if ($quote->closed_at === null) {
                $quote->closed_at = now();
            }
        } else {
            // Reopened — it is no longer a closed deal.
            $quote->closed_at = null;
        }
    }
}
