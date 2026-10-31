<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:koordinator'])
            ->get('/test-role-koordinator', fn () => 'ok');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/test-role-koordinator')->assertRedirect('/login');
    }

    public function test_pelapor_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'pelapor']);

        $this->actingAs($user)
            ->get('/test-role-koordinator')
            ->assertForbidden();
    }

    public function test_koordinator_is_allowed(): void
    {
        $user = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($user)
            ->get('/test-role-koordinator')
            ->assertOk();
    }
}