<?php

namespace Tests\Feature;

use App\Models\MealDistribution;
use App\Models\Report;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportReviewTest extends TestCase
{
    use RefreshDatabase;

    private function makeReport(string $status = 'submitted', string $reporterName = 'Pelapor Uji'): Report
    {
        $pelapor = User::factory()->create(['role' => 'pelapor', 'name' => $reporterName]);
        $school = School::create(['name' => 'SMP Uji', 'address' => 'Alamat uji']);
        $distribution = MealDistribution::create([
            'school_id' => $school->id,
            'distribution_date' => '2026-10-20',
        ]);

        $report = Report::create([
            'user_id' => $pelapor->id,
            'school_id' => $school->id,
            'meal_distribution_id' => $distribution->id,
            'title' => 'Laporan uji detail',
            'incident_description' => 'Deskripsi uji',
            'symptoms' => 'Gejala uji',
            'incident_date' => '2026-10-20',
        ]);

        // status diatur lewat query builder supaya tidak memicu Observer
        Report::whereKey($report->id)->update(['status' => $status]);

        return $report->fresh();
    }

    public function test_pemantau_can_open_report_detail(): void
    {
        $report = $this->makeReport();
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)->get('/reports/'.$report->id)
            ->assertOk()
            ->assertSeeText('Laporan uji detail');
    }

    public function test_detail_does_not_show_reporter_name(): void
    {
        $report = $this->makeReport('submitted', 'Nama Pelapor Rahasia');
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)->get('/reports/'.$report->id)
            ->assertOk()
            ->assertDontSeeText('Nama Pelapor Rahasia');
    }

    public function test_pelapor_cannot_open_report_detail(): void
    {
        $report = $this->makeReport();
        $pelapor = User::factory()->create(['role' => 'pelapor']);

        $this->actingAs($pelapor)->get('/reports/'.$report->id)->assertForbidden();
    }

    public function test_pemantau_can_review_submitted_report(): void
    {
        $report = $this->makeReport('submitted');
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)
            ->post('/reports/'.$report->id.'/review', [
                'monitor_notes' => 'Catatan uji',
                'flagged_for_forwarding' => 1,
            ])
            ->assertRedirect('/reports/'.$report->id);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'under_review',
            'monitor_id' => $pemantau->id,
            'flagged_for_forwarding' => 1,
        ]);
    }

    public function test_review_without_flag_stores_false(): void
    {
        $report = $this->makeReport('submitted');
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)->post('/reports/'.$report->id.'/review', []);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'under_review',
            'flagged_for_forwarding' => 0,
        ]);
    }

    public function test_koordinator_cannot_review(): void
    {
        $report = $this->makeReport('submitted');
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($koordinator)
            ->post('/reports/'.$report->id.'/review', [])
            ->assertForbidden();

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'submitted']);
    }

    public function test_review_is_rejected_when_not_submitted(): void
    {
        $report = $this->makeReport('under_review');
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)
            ->post('/reports/'.$report->id.'/review', [])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'under_review']);
    }
}
