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
    | Warm-up
    |--------------------------------------------------------------------------
    |
    | Cap on messages per calendar day, indexed by how many days this domain has
    | already sent outreach on. A mailbox that normally sends a handful a day
    | going to sixty in one afternoon is the loudest spam signal a small domain
    | can emit -- louder than any wording in the message. The ramp is the single
    | highest-impact thing here, well above subject lines or link counts.
    |
    | The last value repeats once the ramp is exhausted.
    |
    */

    'warmup' => [5, 8, 12, 18, 25, 35, 50],

    /*
    | Local parts that are almost never a person. A pitch landing in a finance
    | inbox is the likeliest message in any scraped list to be reported, and
    | these addresses rarely reply, so they cost reputation and return nothing.
    */
    'role_prefixes' => [
        'accounts', 'account', 'billing', 'finance', 'admin', 'office', 'info',
        'contact', 'enquiry', 'enquiries', 'support', 'help', 'noreply',
        'no-reply', 'webmaster', 'postmaster', 'mail', 'team',
    ],

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
