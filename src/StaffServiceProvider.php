<?php

namespace Hybridcore\Staff;

use App\Services\Extensions\Registries\ExtensionRegistry;
use Hybridcore\Staff\Models\StaffMember;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class StaffServiceProvider extends ServiceProvider
{
    public function boot(ExtensionRegistry $registry): void
    {
        // ── Permissions ──────────────────────────────────────────
        $registry->permissions()->register('staff.view', 'View Staff', 'staff');
        $registry->permissions()->register('staff.manage', 'Manage Staff', 'staff');

        // ── Admin navigation ─────────────────────────────────────
        $registry->navigation()->register(
            label: 'staff::messages.nav',
            route: 'admin.staff.index',
            icon: 'Users',
            section: 'navigation.sections.content',
            permission: 'staff.view',
            sort: 70,
        );

        // ── Public (site header) navigation ──────────────────────
        $registry->publicNavigation()->register(
            label: 'staff::messages.nav',
            route: 'staff.index',
            icon: 'Users',
            sort: 70,
        );

        // ── Dashboard widget: team size ──────────────────────────
        $registry->widgets()->register(
            id: 'staff.count',
            title: 'staff::messages.widget_title',
            component: 'stat',
            data: fn () => [
                'value' => $this->safeCount(),
                'icon' => 'Users',
                'accent' => 'blue',
                'label' => trans('staff::messages.widget_label'),
            ],
            permission: 'staff.view',
            sort: 70,
        );

        // ── Global search: staff members ─────────────────────────
        $registry->search()->register(
            key: 'staff',
            label: 'staff::messages.nav',
            icon: 'Users',
            resolver: fn (string $term, int $limit) => StaffMember::query()
                ->where('is_active', true)
                ->where(fn ($q) => $q->where('name', 'like', '%'.$term.'%')
                    ->orWhere('role_label', 'like', '%'.$term.'%'))
                ->orderBy('sort')
                ->limit($limit)
                ->get()
                ->map(fn (StaffMember $m) => [
                    'title' => $m->name,
                    'url' => route('staff.index'),
                    'meta' => $m->role_label,
                ])->all(),
        );
    }

    /** Total active staff, resilient to the table not existing yet. */
    private function safeCount(): int
    {
        try {
            return Schema::hasTable('staff_members')
                ? StaffMember::where('is_active', true)->count()
                : 0;
        } catch (\Throwable) {
            return 0;
        }
    }
}
