<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_regular_user_is_rejected_from_admin_dashboard(): void
    {
        $user = User::factory()->regularUser()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_can_access_admin_only_pages(): void
    {
        $admin = User::factory()->admin()->create();

        foreach ([
            route('admin.dashboard'),
            route('admin.users'),
            route('admin.announcement'),
            route('admin.events'),
            route('admin.teams'),
            route('admin.reports'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_satpam_can_access_lostfound_but_not_admin_only_pages(): void
    {
        $satpam = User::factory()->satpam()->create();

        $this->actingAs($satpam)->get(route('admin.lostfound'))->assertOk();
        $this->actingAs($satpam)->get(route('admin.dashboard'))->assertForbidden();
    }
}
