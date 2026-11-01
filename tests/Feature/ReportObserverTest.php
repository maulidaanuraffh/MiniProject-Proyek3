<?php

namespace Tests\Feature;

use App\Models\MealDistribution;
use App\Models\Report;
use App\Models\ReportStatusLog;
use App\Models\School;
use App\Models\User;
use App\Services\ReportStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportObserverTest extends TestCase
{
    use RefreshDatabase;

    private function makeReport(): Report
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $school = School::create(['name' => 'SMP Uji', 'address' => 'Alamat uji']);
        $distribution = MealDistribution::create([
            'school_id' => $school->id,
            'distribution_date' => '2026-10-20',
        ]);

        return Report::create([
            'user_id' => $pelapor->id,
            'school_id' => $school->id,
            'meal_distribution_id' => $distribution->id,
            'title' => 'Laporan uji',
            'incident_description' => 'Deskripsi uji',
            'symptoms' => 'Gejala uji',
            'incident_date' => '2026-10-20',
        ])->fresh();
    }

    public function test_status_change_is_logged(): void
    {
        $report = $this->makeReport();
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau);
        (new ReportStatusService())->review($report, $pemantau, 'Catatan uji', false);

        $this->assertDatabaseHas('report_status_logs', [
            'report_id' => $report->id,
            'changed_by_user_id' => $pemantau->id,
            'old_status' => 'submitted',
            'new_status' => 'under_review',
        ]);
    }

    public function test_editing_non_status_field_is_not_logged(): void
    {
        $report = $this->makeReport();

        $report->title = 'Judul diubah';
        $report->save();

        $this->assertDatabaseCount('report_status_logs', 0);
    }

    public function test_rejected_transition_is_not_logged(): void
    {
        $report = $this->makeReport();

        try {
            (new ReportStatusService())->close($report, User::factory()->create(['role' => 'koordinator']));
        } catch (\Illuminate\Validation\ValidationException $e) {
            // diharapkan
        }

        $this->assertDatabaseCount('report_status_logs', 0);
    }

    public function test_log_does_not_contain_sensitive_content(): void
    {
        $report = $this->makeReport();
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        (new ReportStatusService())->review($report, $pemantau, 'Catatan rahasia', false);

        $log = ReportStatusLog::firstOrFail();
        $this->assertSame(
            ['id', 'report_id', 'changed_by_user_id', 'old_status', 'new_status', 'created_at'],
            array_keys($log->getAttributes()),
        );
    }
}
