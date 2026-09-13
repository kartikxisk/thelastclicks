<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sender identity
    |--------------------------------------------------------------------------
    |
    | Cold outreach is signed by a person, not a brand -- a mail from "Sales
    | Team" reads as a blast and gets treated like one. Reply-to must be a
    | mailbox someone actually watches; every reply and every bounce lands
    | there, and there is no IMAP automation reading it for you.
    |
    */

    'from_name' => env('OUTREACH_FROM_NAME', 'Kartik'),
    'reply_to' => env('OUTREACH_REPLY_TO', 'info@thelastclicks.com'),
    'signature_phone' => env('OUTREACH_PHONE', '+91 87701 55842'),
    'signature_site' => env('OUTREACH_SITE', 'thelastclicks.com'),
    'signature_instagram' => env('OUTREACH_INSTAGRAM', '@thelastclicks'),

    /*
    |--------------------------------------------------------------------------
    | Throttle
    |--------------------------------------------------------------------------
    |
    | One mail every 90s +/- jitter. 62 contacts lands around 1.5 hours, which
    | is the point: a burst of 62 identical-shaped messages from a mailbox that
    | normally sends a handful a day is the single loudest spam signal a small
    | domain can emit. Jitter keeps the send times off a machine-perfect grid.
    |
    */

    'throttle_seconds' => (int) env('OUTREACH_THROTTLE', 90),
    'throttle_jitter' => (int) env('OUTREACH_JITTER', 25),

    /*
    | Hard ceiling per invocation, whatever --limit says. A runaway loop here
    | burns the domain reputation that the SPF/DKIM/DMARC setup exists to
    | protect, and reputation recovers a great deal slower than it burns.
    */
    'max_per_run' => (int) env('OUTREACH_MAX_PER_RUN', 80),

    /*
    |--------------------------------------------------------------------------
    | Cadence
    |--------------------------------------------------------------------------
    |
    | Mirrored in build_leads.py, which computes what is due. Changing these
    | without changing the Python side means the queue and the sender disagree
    | about the schedule.
    |
    */

    'steps' => ['first-touch', 'follow-up-1', 'follow-up-2'],

    'follow_up_days' => [
        'first-touch' => 4,   // days until follow-up-1 becomes due
        'follow-up-1' => 7,   // days until follow-up-2 becomes due
        'follow-up-2' => null, // end of cadence -- never a third nudge
    ],

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | These live in marketing-sales/, which is gitignored: the queue holds real
    | contact data and must never reach the repo. The code is tracked, the
    | people are not.
    |
    */

    'queue_path' => base_path('marketing-sales/data/outreach-queue.csv'),
    'sent_path' => base_path('marketing-sales/data/outreach-sent.csv'),
    'suppression_path' => base_path('marketing-sales/data/suppression.txt'),

    /*
    |--------------------------------------------------------------------------
    | Preflight expectations
    |--------------------------------------------------------------------------
    |
    | DKIM selectors are GoDaddy's; they publish as CNAMEs to onsecureserver.net
    | rather than as TXT on the domain itself, so a naive TXT lookup on
    | "default._domainkey" finds nothing and wrongly reports DKIM missing.
    |
    */

    'dkim_selectors' => ['secureserver1', 'secureserver2'],

];
