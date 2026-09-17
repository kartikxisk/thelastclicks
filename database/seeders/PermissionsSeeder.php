<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Facades\Filament;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Generate per-resource permissions for all auto-discovered Filament Resources.
        // --ignore-existing-policies preserves any custom policy logic (e.g. QuotePolicy).
        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--ignore-existing-policies' => true,
        ]);

        $this->assignRolePermissions();
    }

    /**
     * The lead desk, pipeline board and their widgets. Shield generates one
     * permission per page/widget, so they have to be handed to the lead-facing
     * roles explicitly — otherwise only Super-admin ever sees the sales console.
     *
     * @var list<string>
     */
    protected array $leadDeskPermissions = [
        'page_LeadDesk',
        'page_LeadPipeline',
        'widget_LeadStatsWidget',
        'widget_LeadsTrendChart',
        'widget_PipelineFunnelChart',
        'widget_NeedsAttentionTable',
        'widget_AssigneeWorkloadWidget',
        'widget_RecentActivityWidget',
    ];

    /**
     * The Billing nav group, read off the panel, as an alternation of the
     * permission suffixes shield derives from each resource class name.
     *
     * Derived rather than listed because Viewer's carve-out below fails OPEN
     * against a literal list. Phase 2's `invoice`, `invoice::line` and
     * `invoice::payment` would match no list written today, so Viewer's
     * blanket `view_*` grant would silently hand every read-only account the
     * invoice resource — bank details, client GSTINs, revenue. The Accounts
     * side of the same property fails closed, because the resource simply
     * stays invisible and someone says so on day one; only the Viewer
     * direction goes unnoticed. Reading the group off the panel makes the
     * property "Viewer holds nothing for anything in Billing" rather than
     * "Viewer holds nothing for these three names".
     *
     * Shield derives the suffix from the resource name, so two-word models
     * land as `billing::client`, not `billing_client`. Matching the underscore
     * form silently grants nothing.
     */
    protected function billingPermissionPattern(): string
    {
        $identifiers = collect(Filament::getPanel('admin')->getResources())
            ->filter(fn (string $resource): bool => $resource::getNavigationGroup() === 'Billing')
            ->map(fn (string $resource): string => preg_quote(FilamentShield::getPermissionIdentifier($resource), '/'))
            ->values();

        // An empty alternation would compile to `/_()$/` and match nothing
        // useful for Accounts while matching the empty string for Viewer's
        // negated test — i.e. the fail-open case this method exists to close.
        // `(?!)` is a pattern that can never match, which is the safe reading
        // of "there are no billing resources".
        return $identifiers->isEmpty() ? '(?!)' : $identifiers->implode('|');
    }

    /**
     * Public because it is the half of this seeder that can be re-run on its
     * own — shield:generate creates the permissions, this hands them out — and
     * a test that registers a phase-2 resource onto the panel needs to re-run
     * exactly this half against it.
     */
    public function assignRolePermissions(): void
    {
        $superAdmin = Role::findOrCreate('Super-admin', 'web');
        $editor = Role::findOrCreate('Editor', 'web');
        $sales = Role::findOrCreate('Sales', 'web');
        $viewer = Role::findOrCreate('Viewer', 'web');
        $accounts = Role::findOrCreate('Accounts', 'web');

        $all = Permission::pluck('name')->all();

        $billing = $this->billingPermissionPattern();

        // Only hand out lead-desk permissions that shield:generate actually created.
        $leadDesk = array_values(array_intersect($this->leadDeskPermissions, $all));

        // Super-admin: everything
        $superAdmin->syncPermissions($all);

        // Editor: CRUD on content resources (post, portfolio, service, industry,
        // category, tag, testimonial, work, client, hero slide).
        // `hero::slide` is shield's name for HeroSlide. Without it the Homepage
        // Hero resource exists but is invisible to every role except Super-admin,
        // which reads as "the upload screen isn't there" rather than as a
        // permission problem.
        $editorResources = 'post|service|industry|category|tag|testimonial|work|client|hero::slide';
        $editor->syncPermissions(array_filter($all, fn ($p) => preg_match('/_('.$editorResources.')$/', $p) === 1
            || preg_match('/_any_('.$editorResources.')$/', $p) === 1
        ));

        // Sales: all perms on Quote + QuoteNote + Subscriber (all inbound lead data),
        // plus outreach, which is the outbound half of the same job, plus the
        // lead desk and pipeline they work from every day. Without the outreach
        // permissions here the resource exists and only Super-admin can see it,
        // which is the one role that never runs the campaign.
        $sales->syncPermissions(array_merge(
            array_filter($all, fn ($p) => str_ends_with($p, '_quote')
                || str_ends_with($p, '_quote_note')
                || str_ends_with($p, '_subscriber')
                // Shield derives the suffix from the resource name, so a
                // two-word model lands as "outreach::contact", not
                // "outreach_contact". Matching the underscore form silently
                // grants nothing and the resource stays Super-admin-only.
                || str_ends_with($p, '_outreach::contact')),
            $leadDesk,
        ));

        // Accounts: the billing surface, and only that. Invoices carry bank
        // details and client GSTINs, which is a narrower audience than content
        // or leads.
        $accounts->syncPermissions(array_filter(
            $all,
            fn ($p) => preg_match('/_('.$billing.')$/', $p) === 1
        ));

        // Viewer: read-only everywhere, including the lead desk. Moving a card is
        // still refused by QuotePolicy::update, which Viewer never satisfies.
        // Billing is carved out: a blanket `view_*` grant would hand every
        // read-only account our bank details and every client's GSTIN.
        $viewer->syncPermissions(array_merge(
            array_filter(
                $all,
                fn ($p) => str_starts_with($p, 'view_')
                    && preg_match('/_('.$billing.')$/', $p) !== 1
            ),
            $leadDesk,
        ));
    }
}
