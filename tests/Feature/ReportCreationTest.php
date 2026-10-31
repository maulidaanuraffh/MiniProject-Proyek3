<?php

namespace Tests\Feature;

use App\Models\MealDistribution;
use App\Models\Report;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCreationTest extends TestCase
{
    use RefreshDatabase;

    private function makeSchoolAndDistribution(): array
    {
        $school = School::create(['name' => 'SMP Uji', 'address' => 'Alamat uji']);
        $distribution = MealDistribution::create([
            'school_id' => $school->id,
            'distribution_date' => '2026-10-20',
        ]);

        return [$school, $distribution];
    }

    private function validPayload(School $school, MealDistribution $distribution): array
    {
        return [
            'school_id' => $school->id,
            'meal_distribution_id' => $distribution->id,
            'title' => 'Keluhan uji',
            'incident_description' => 'Deskripsi kejadian uji',
            'symptoms' => 'Mual dan sakit perut',
            'affected_count' => 3,
            'incident_date' => '2026-10-20',
        ];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/reports/create')->assertRedirect('/login');
    }

    public function test_pemantau_cannot_access_create_form(): void
    {
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)->get('/reports/create')->assertForbidden();
    }

    public function test_pelapor_can_open_create_form(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);

        $this->actingAs($pelapor)->get('/reports/create')->assertOk();
    }

    public function test_pelapor_can_submit_valid_report(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        [$school, $distribution] = $this->makeSchoolAndDistribution();

        $this->actingAs($pelapor)
            ->post('/reports', $this->validPayload($school, $distribution))
            ->assertRedirect('/reports/create');

        $this->assertDatabaseHas('reports', [
            'user_id' => $pelapor->id,
            'school_id' => $school->id,
            'meal_distribution_id' => $distribution->id,
            'status' => 'submitted',
        ]);
    }

    public function test_invalid_report_is_rejected_and_not_saved(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        [$school, $distribution] = $this->makeSchoolAndDistribution();

        $payload = $this->validPayload($school, $distribution);
        $payload['title'] = '';

        $this->actingAs($pelapor)
            ->post('/reports', $payload)
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_future_incident_date_is_rejected(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        [$school, $distribution] = $this->makeSchoolAndDistribution();

        $payload = $this->validPayload($school, $distribution);
        $payload['incident_date'] = now()->addDay()->toDateString();

        $this->actingAs($pelapor)
            ->post('/reports', $payload)
            ->assertSessionHasErrors('incident_date');

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_client_cannot_set_owner_or_status(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $other = User::factory()->create(['role' => 'pelapor']);
        [$school, $distribution] = $this->makeSchoolAndDistribution();

        $payload = $this->validPayload($school, $distribution);
        $payload['user_id'] = $other->id;
        $payload['status'] = 'closed';

        $this->actingAs($pelapor)->post('/reports', $payload);

        $report = Report::firstOrFail();
        $this->assertSame($pelapor->id, $report->user_id);
        $this->assertSame('submitted', $report->status);
    }
}
