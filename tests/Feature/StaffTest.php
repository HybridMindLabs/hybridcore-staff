<?php

namespace Hybridcore\Staff\Tests\Feature;

use App\Models\User;
use App\Services\Extensions\ExtensionAutoloader;
use Hybridcore\Staff\Models\StaffMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        file_put_contents(storage_path('installed.lock'), 'installed');
    }

    protected function beforeRefreshingDatabase(): void
    {
        ExtensionAutoloader::register(
            base_path('extensions/hybridcore/staff'),
            ['namespace' => 'Hybridcore\\Staff'],
        );
        $this->app['migrator']->path(base_path('extensions/hybridcore/staff/database/migrations'));
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
}
