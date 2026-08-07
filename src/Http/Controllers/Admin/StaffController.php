<?php

namespace Hybridcore\Staff\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Hybridcore\Staff\Models\StaffMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function index(): Response
    {
        $members = StaffMember::with('user:id,username,avatar')
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (StaffMember $m) => [
                'id' => $m->id,
                'user_id' => $m->user_id,
                'name' => $m->name,
                'role_label' => $m->role_label,
                'group' => $m->group,
                'bio' => $m->bio,
                'avatar' => $m->displayAvatar(),
                'color' => $m->color,
                'discord' => $m->discord,
                'steam' => $m->steam,
                'is_active' => $m->is_active,
                'sort' => $m->sort,
                'linked_username' => $m->user?->username,
            ]);

        return Inertia::render('Extensions/hybridcore/staff/Admin/Index', [
            'members' => $members,
        ]);
    }

    /** Autocomplete for the "linked user" picker. */
    public function users(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        $users = User::query()
            ->whereNull('banned_at')
            ->whereNotNull('username')
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('username', 'like', '%'.$term.'%')
                ->orWhere('display_name', 'like', '%'.$term.'%')))
            ->orderBy('username')
            ->limit(10)
            ->get(['id', 'username', 'display_name', 'avatar'])
            ->map(fn (User $u) => [
                'id' => $u->id,
                'username' => $u->username,
                'display_name' => $u->display_name ?: $u->username,
                'avatar' => $u->avatar,
            ]);

        return response()->json(['users' => $users]);
    }

    public function store(Request $request): RedirectResponse
    {
        StaffMember::create($this->validated($request));

        return back()->with('success', trans('staff::messages.created'));
    }

    public function update(Request $request, StaffMember $member): RedirectResponse
    {
        $member->update($this->validated($request));

        return back()->with('success', trans('staff::messages.updated'));
    }

    public function destroy(StaffMember $member): RedirectResponse
    {
        $member->delete();

        return back()->with('success', trans('staff::messages.deleted'));
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:80'],
            'role_label' => ['required', 'string', 'max:60'],
            'group' => ['nullable', 'string', 'max:60'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'url', 'max:500'],
            'color' => ['nullable', 'string', 'max:20'],
            'discord' => ['nullable', 'string', 'max:120'],
            'steam' => ['nullable', 'url', 'max:255'],
            'is_active' => ['boolean'],
            'sort' => ['integer', 'min:0', 'max:9999'],
        ]);
    }
}
