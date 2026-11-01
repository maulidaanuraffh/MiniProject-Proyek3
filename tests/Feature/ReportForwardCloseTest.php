<?php

namespace Tests\Feature;

use App\Models\MealDistribution;
use App\Models\Report;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportForwardCloseTest extends TestCase
{
    use RefreshDatabase;

    private function makeReport(string $status, bool $flagged = false): Report
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $school = School::create(['name' => 'SMP Uji', 'address' => 'Alamat uji']);
        $distribution = MealDistribution::create([
            'school_id' => $school->id,
            'distribution_date' => '2026-10-20',
        ]);

        $report = Report::create([
            'user_id' => $pelapor->id,
            'school_id' => $school->id,
            'meal_distribution_id' => $distribution->id,
            'title' => 'Laporan uji teruskan',
            'incident_description' => 'Deskripsi uji',
            'symptoms' => 'Gejala uji',
            'incident_date' => '2026-10-20',
        ]);

        // status dan flag diatur lewat query builder supaya tidak memicu Observer
        Report::whereKey($report->id)->update([
            'status' => $status,
            'flagged_for_forwarding' => $flagged,
        ]);

        return $report->fresh();
    }

    public function test_koordinator_can_forward_flagged_report(): void
    {
        $report = $this->makeReport('under_review', true);
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($koordinator)
            ->post('/reports/'.$report->id.'/forward', [
                'forwarded_to' => 'Sekolah',
                'coordinator_notes' => 'Diteruskan ke kepala sekolah',
            ])
            ->assertRedirect('/reports/'.$report->id);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'forwarded',
            'forwarded_to' => 'Sekolah',
        ]);
    }

    public function test_forward_rejected_when_not_flagged(): void
    {
        $report = $this->makeReport('under_review', false);
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($koordinator)
            ->post('/reports/'.$report->id.'/forward', ['forwarded_to' => 'Sekolah'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'under_review']);
    }

    public function test_forward_rejects_unknown_target(): void
    {
        $report = $this->makeReport('under_review', true);
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($koordinator)
            ->post('/reports/'.$report->id.'/forward', ['forwarded_to' => 'Pihak Ngawur'])
            ->assertSessionHasErrors('forwarded_to');

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'under_review']);
    }

    public function test_pemantau_cannot_forward(): void
    {
        $report = $this->makeReport('under_review', true);
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)
            ->post('/reports/'.$report->id.'/forward', ['forwarded_to' => 'Sekolah'])
            ->assertForbidden();

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'under_review']);
    }

    public function test_koordinator_can_close_forwarded_report(): void
    {
        $report = $this->makeReport('forwarded', true);
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($koordinator)
            ->post('/reports/'.$report->id.'/close')
            ->assertRedirect('/reports/'.$report->id);

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'closed']);
    }

    public function test_close_rejected_when_not_forwarded(): void
    {
        $report = $this->makeReport('under_review', true);
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($koordinator)
            ->post('/reports/'.$report->id.'/close')
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'under_review']);
    }

    public function test_pemantau_cannot_close(): void
    {
        $report = $this->makeReport('forwarded', true);
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)
            ->post('/reports/'.$report->id.'/close')
            ->assertForbidden();

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'forwarded']);
    }

    public function test_pelapor_cannot_forward_or_close(): void
    {
        $report = $this->makeReport('forwarded', true);
        $pelapor = User::factory()->create(['role' => 'pelapor']);

        $this->actingAs($pelapor)
            ->post('/reports/'.$report->id.'/forward', ['forwarded_to' => 'Sekolah'])
            ->assertForbidden();

        $this->actingAs($pelapor)
            ->post('/reports/'.$report->id.'/close')
            ->assertForbidden();

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'forwarded']);
    }
}
