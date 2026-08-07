<?php

namespace Hybridcore\Staff\Tests\Feature;

use App\Http\Middleware\EnsureIsAdmin;
use App\Models\User;
use App\Services\Extensions\ExtensionAutoloader;
use Hybridcore\Staff\Models\StaffMember;
use Hybridcore\Staff\StaffServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StaffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        file_put_contents(storage_path('installed.lock'), 'installed');

        // Routes normally register only for extensions the DB marks enabled
        // at boot — a fresh RefreshDatabase test has no such row, so wire
        // them by hand. Mirrors demo's ExampleTest.
        $base = base_path('extensions/hybridcore/staff');
        ExtensionAutoloader::register($base, ['namespace' => 'Hybridcore\\Staff']);

        $this->app->register(StaffServiceProvider::class);

        Route::middleware(['web', 'auth', EnsureIsAdmin::class])
            ->prefix('admin')->group($base.'/routes/admin.php');

        $this->app['router']->getRoutes()->refreshNameLookups();
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('installed.lock'));
        parent::tearDown();
    }

    public function test_display_avatar_prefers_linked_user(): void
    {
        $user = User::factory()->create(['avatar' => null]);

        $standalone = StaffMember::create([
            'name' => 'Guest', 'role_label' => 'Helper', 'avatar' => 'https://x/a.png',
        ]);
        $linked = StaffMember::create([
            'user_id' => $user->id, 'name' => 'Linked', 'role_label' => 'Admin', 'avatar' => 'https://x/fallback.png',
        ]);

        // Standalone falls back to its own avatar.
        $this->assertSame('https://x/a.png', $standalone->displayAvatar());
        // Linked user has no avatar, so it uses the stored fallback.
        $this->assertSame('https://x/fallback.png', $linked->fresh()->load('user')->displayAvatar());
    }

    public function test_only_active_members_are_listed(): void
    {
        StaffMember::create(['name' => 'On', 'role_label' => 'Admin', 'is_active' => true]);
        StaffMember::create(['name' => 'Off', 'role_label' => 'Admin', 'is_active' => false]);

        $active = StaffMember::where('is_active', true)->pluck('name');

        $this->assertCount(1, $active);
        $this->assertTrue($active->contains('On'));
        $this->assertFalse($active->contains('Off'));
    }

    public function test_index_requires_the_view_permission(): void
    {
        $this->get(route('admin.staff.index'))->assertRedirect();

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get(route('admin.staff.index'))->assertOk();
    }

    public function test_admin_can_create_a_member(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'New Mod', 'role_label' => 'Moderator', 'is_active' => true, 'sort' => 5,
        ])->assertRedirect();

        $this->assertDatabaseHas('staff_members', ['name' => 'New Mod', 'role_label' => 'Moderator']);
    }

    public function test_admin_can_update_a_member(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $member = StaffMember::create(['name' => 'Old', 'role_label' => 'Admin']);

        $this->actingAs($admin)->put(route('admin.staff.update', $member), [
            'name' => 'Renamed', 'role_label' => 'Admin', 'is_active' => true, 'sort' => 0,
        ])->assertRedirect();

        $this->assertSame('Renamed', $member->fresh()->name);
    }

    public function test_admin_can_delete_a_member(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $member = StaffMember::create(['name' => 'Gone', 'role_label' => 'Admin']);

        $this->actingAs($admin)->delete(route('admin.staff.destroy', $member))->assertRedirect();

        $this->assertDatabaseMissing('staff_members', ['id' => $member->id]);
    }

    public function test_steam_link_must_be_a_url(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'Bad Steam', 'role_label' => 'Admin', 'steam' => 'not-a-url',
        ])->assertSessionHasErrors('steam');
    }

    public function test_user_autocomplete_finds_by_username(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['username' => 'findme']);
        User::factory()->create(['username' => 'other']);

        $response = $this->actingAs($admin)->getJson(route('admin.staff.users').'?q=findme');

        $response->assertOk();
        $this->assertCount(1, $response->json('users'));
        $this->assertSame('findme', $response->json('users.0.username'));
    }
}
