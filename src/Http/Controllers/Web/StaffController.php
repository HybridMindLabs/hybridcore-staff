<?php

namespace Hybridcore\Staff\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Hybridcore\Staff\Models\StaffMember;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function index(): Response
    {
        $members = StaffMember::where('is_active', true)
            ->with('user:id,username')
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (StaffMember $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'role_label' => $m->role_label,
                'group' => $m->group,
                'bio' => $m->bio,
                'avatar' => $m->displayAvatar(),
                'color' => $m->color,
                'discord' => $m->discord,
                'steam' => $m->steam,
                'profile_url' => $m->profileUrl(),
            ]);

        // Preserve first-seen group order; ungrouped members fall under null.
        $groups = $members->groupBy('group')->map(fn ($items, $group) => [
            'group' => $group ?: null,
            'members' => $items->values(),
        ])->values();

        return Inertia::render('Extensions/hybridcore/staff/Web/Index', [
            'groups' => $groups,
            'count' => $members->count(),
        ]);
    }
}
