<?php

namespace Hybridcore\Staff\Database\Seeders;

use App\Models\User;
use Hybridcore\Staff\Models\StaffMember;
use Illuminate\Database\Seeder;

/**
 * Seeds one example staff member linked to the first admin, so the page isn't
 * empty on install. Idempotent — safe to run on every enable.
 */
class DefaultSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('is_admin', true)->orderBy('id')->first();

        StaffMember::firstOrCreate(
            ['role_label' => 'Founder', 'name' => $admin?->username ?? 'Founder'],
            [
                'user_id' => $admin?->id,
                'group' => 'Management',
                'bio' => 'Runs the community.',
                'color' => '#3b82f6',
                'is_active' => true,
                'sort' => 0,
            ],
        );
    }
}
