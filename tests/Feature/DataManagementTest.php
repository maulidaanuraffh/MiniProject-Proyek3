<?php

namespace Tests\Feature;

use App\Models\MealDistribution;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_koordinator_can_open_school_page(): void
    {
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($koordinator)->get('/kelola/sekolah')->assertOk();
    }

    public function test_pemantau_and_pelapor_cannot_open_school_page(): void
    {
        $pemantau = User::factory()->create(['role' => 'pemantau']);
        $pelapor = User::factory()->create(['role' => 'pelapor']);

        $this->actingAs($pemantau)->get('/kelola/sekolah')->assertForbidden();
        $this->actingAs($pelapor)->get('/kelola/sekolah')->assertForbidden();
    }

    public function test_koordinator_can_add_school(): void
    {
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($koordinator)
            ->post('/kelola/sekolah', ['name' => 'SMP Baru', 'address' => 'Alamat baru'])
            ->assertRedirect('/kelola/sekolah');

        $this->assertDatabaseHas('schools', ['name' => 'SMP Baru', 'address' => 'Alamat baru']);
    }

    public function test_duplicate_school_name_is_rejected(): void
    {
        School::create(['name' => 'SMP Sama', 'address' => 'Alamat']);
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($koordinator)
            ->post('/kelola/sekolah', ['name' => 'SMP Sama'])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('schools', 1);
    }

    public function test_pemantau_cannot_add_school(): void
    {
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)
            ->post('/kelola/sekolah', ['name' => 'SMP Ilegal'])
            ->assertForbidden();

        $this->assertDatabaseCount('schools', 0);
    }

    public function test_koordinator_can_add_distribution_to_school(): void
    {
        $school = School::create(['name' => 'SMP Uji', 'address' => 'Alamat']);
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($koordinator)
            ->post('/kelola/sekolah/'.$school->id.'/distribusi', [
                'distribution_date' => '2026-11-10',
                'description' => 'Menu uji',
            ])
            ->assertRedirect('/kelola/sekolah/'.$school->id.'/distribusi');

        $this->assertDatabaseHas('meal_distributions', [
            'school_id' => $school->id,
            'distribution_date' => '2026-11-10 00:00:00',
            'description' => 'Menu uji',
        ]);
    }

    public function test_duplicate_distribution_date_for_same_school_is_rejected(): void
    {
        $school = School::create(['name' => 'SMP Uji', 'address' => 'Alamat']);
        MealDistribution::create(['school_id' => $school->id, 'distribution_date' => '2026-11-10']);
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($koordinator)
            ->post('/kelola/sekolah/'.$school->id.'/distribusi', ['distribution_date' => '2026-11-10'])
            ->assertSessionHasErrors('distribution_date');

        $this->assertDatabaseCount('meal_distributions', 1);
    }

    public function test_same_date_is_allowed_for_different_school(): void
    {
        $schoolA = School::create(['name' => 'SMP A', 'address' => 'Alamat A']);
        $schoolB = School::create(['name' => 'SMP B', 'address' => 'Alamat B']);
        MealDistribution::create(['school_id' => $schoolA->id, 'distribution_date' => '2026-11-10']);
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($koordinator)
            ->post('/kelola/sekolah/'.$schoolB->id.'/distribusi', ['distribution_date' => '2026-11-10'])
            ->assertRedirect();

        $this->assertDatabaseCount('meal_distributions', 2);
    }

    public function test_pelapor_cannot_add_distribution(): void
    {
        $school = School::create(['name' => 'SMP Uji', 'address' => 'Alamat']);
        $pelapor = User::factory()->create(['role' => 'pelapor']);

        $this->actingAs($pelapor)
            ->post('/kelola/sekolah/'.$school->id.'/distribusi', ['distribution_date' => '2026-11-10'])
            ->assertForbidden();

        $this->assertDatabaseCount('meal_distributions', 0);
    }
}
