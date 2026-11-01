<?php

namespace Tests\Feature;

use App\Models\MealDistribution;
use App\Models\Report;
use App\Models\School;
use App\Models\User;
use App\Services\ReportStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReportStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReportStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ReportStatusService();
    }

    private function makeUser(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function makeReport(string $status = 'submitted'): Report
    {
        $pelapor = $this->makeUser('pelapor');
        $school = School::create(['name' => 'SMP Uji', 'address' => 'Alamat uji']);
        $distribution = MealDistribution::create([
            'school_id' => $school->id,
            'distribution_date' => '2026-10-20',
        ]);

        $report = Report::create([
            'user_id' => $pelapor->id,
            'school_id' => $school->id,
            'meal_distribution_id' => $distribution->id,
            'title' => 'Laporan uji',
            'incident_description' => 'Deskripsi uji',
            'symptoms' => 'Gejala uji',
            'incident_date' => '2026-10-20',
        ]);

        // status tidak ada di $fillable, jadi diatur langsung
        $report->status = $status;
        $report->save();

        return $report->fresh();
    }

    public function test_pemantau_can_review_submitted_report(): void
    {
        $report = $this->makeReport('submitted');
        $pemantau = $this->makeUser('pemantau');

        $this->service->review($report, $pemantau, 'Catatan uji', true);

        $report->refresh();
        $this->assertSame('under_review', $report->status);
        $this->assertSame($pemantau->id, $report->monitor_id);
        $this->assertTrue($report->flagged_for_forwarding);
    }

    public function test_pelapor_cannot_review(): void
    {
        $report = $this->makeReport('submitted');

        $this->expectException(ValidationException::class);
        $this->service->review($report, $this->makeUser('pelapor'), null, false);
    }

    public function test_report_cannot_skip_to_forwarded(): void
    {
        $report = $this->makeReport('submitted');

        try {
            $this->service->forward($report, $this->makeUser('koordinator'), 'Puskesmas', null);
            $this->fail('Seharusnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertSame('submitted', $report->fresh()->status);
        }
    }

    public function test_forward_requires_flag_from_monitor(): void
    {
        $report = $this->makeReport('submitted');
        $this->service->review($report, $this->makeUser('pemantau'), null, false);

        try {
            $this->service->forward($report->fresh(), $this->makeUser('koordinator'), 'Puskesmas', null);
            $this->fail('Seharusnya ditolak karena belum ditandai.');
        } catch (ValidationException $e) {
            $this->assertSame('under_review', $report->fresh()->status);
        }
    }

    public function test_koordinator_forwards_flagged_report(): void
    {
        $report = $this->makeReport('submitted');
        $this->service->review($report, $this->makeUser('pemantau'), null, true);

        $this->service->forward($report->fresh(), $this->makeUser('koordinator'), 'Puskesmas', 'Diteruskan');

        $report->refresh();
        $this->assertSame('forwarded', $report->status);
        $this->assertSame('Puskesmas', $report->forwarded_to);
    }

    public function test_pemantau_cannot_forward(): void
    {
        $report = $this->makeReport('under_review');
        $report->flagged_for_forwarding = true;
        $report->save();

        $this->expectException(ValidationException::class);
        $this->service->forward($report->fresh(), $this->makeUser('pemantau'), 'Puskesmas', null);
    }

    public function test_report_cannot_close_before_forwarded(): void
    {
        $report = $this->makeReport('under_review');

        try {
            $this->service->close($report, $this->makeUser('koordinator'));
            $this->fail('Seharusnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertSame('under_review', $report->fresh()->status);
        }
    }

    public function test_koordinator_closes_forwarded_report(): void
    {
        $report = $this->makeReport('forwarded');

        $this->service->close($report, $this->makeUser('koordinator'));

        $this->assertSame('closed', $report->fresh()->status);
    }
}
