<?php

namespace Database\Seeders;

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

    protected function assignRolePermissions(): void
    {
        $superAdmin = Role::findOrCreate('Super-admin', 'web');
        $editor = Role::findOrCreate('Editor', 'web');
        $sales = Role::findOrCreate('Sales', 'web');
        $viewer = Role::findOrCreate('Viewer', 'web');

        $all = Permission::pluck('name')->all();

        // Only hand out lead-desk permissions that shield:generate actually created.
        $leadDesk = array_values(array_intersect($this->leadDeskPermissions, $all));

        // Super-admin: everything
        $superAdmin->syncPermissions($all);

        // Editor: CRUD on content resources (post, portfolio, service, industry,
        // category, tag, testimonial, work, client).
        $editorResources = 'post|service|industry|category|tag|testimonial|work|client';
        $editor->syncPermissions(array_filter($all, fn ($p) => preg_match('/_('.$editorResources.')$/', $p) === 1
            || preg_match('/_any_('.$editorResources.')$/', $p) === 1
        ));

        // Sales: all perms on Quote + QuoteNote + Subscriber (all inbound lead data),
        // plus the lead desk and pipeline they work from every day.
        $sales->syncPermissions(array_merge(
            array_filter($all, fn ($p) => str_ends_with($p, '_quote') || str_ends_with($p, '_quote_note') || str_ends_with($p, '_subscriber')),
            $leadDesk,
        ));

        // Viewer: read-only everywhere, including the lead desk. Moving a card is
        // still refused by QuotePolicy::update, which Viewer never satisfies.
        $viewer->syncPermissions(array_merge(
            array_filter($all, fn ($p) => str_starts_with($p, 'view_')),
            $leadDesk,
        ));
    }
}
