<?php

namespace Tests\Feature;

use App\Models\MealDistribution;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolDistributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_fetch_distributions(): void
    {
        $school = School::create(['name' => 'SMP Uji', 'address' => 'Alamat uji']);

        $this->getJson("/schools/{$school->id}/distributions")->assertUnauthorized();
    }

    public function test_returns_only_distributions_of_selected_school(): void
    {
        $user = User::factory()->create(['role' => 'pelapor']);
        $schoolA = School::create(['name' => 'SMP A', 'address' => 'Alamat A']);
        $schoolB = School::create(['name' => 'SMP B', 'address' => 'Alamat B']);

        $distA = MealDistribution::create([
            'school_id' => $schoolA->id,
            'distribution_date' => '2026-10-20',
        ]);
        MealDistribution::create([
            'school_id' => $schoolB->id,
            'distribution_date' => '2026-10-20',
        ]);

        $this->actingAs($user)
            ->getJson("/schools/{$schoolA->id}/distributions")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $distA->id);
    }

    public function test_unknown_school_returns_not_found(): void
    {
        $user = User::factory()->create(['role' => 'pelapor']);

        $this->actingAs($user)
            ->getJson('/schools/999/distributions')
            ->assertNotFound();
    }
}
