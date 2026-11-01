<?php

namespace Tests\Feature;

use App\Models\MealDistribution;
use App\Models\Report;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporterOwnReportsTest extends TestCase
{
    use RefreshDatabase;

    private function makeReportFor(User $owner, string $title): Report
    {
        $school = School::firstOrCreate(['name' => 'SMP Uji'], ['address' => 'Alamat uji']);
        $distribution = MealDistribution::create([
            'school_id' => $school->id,
            'distribution_date' => '2026-10-20',
        ]);

        return Report::create([
            'user_id' => $owner->id,
            'school_id' => $school->id,
            'meal_distribution_id' => $distribution->id,
            'title' => $title,
            'incident_description' => 'Deskripsi uji',
            'symptoms' => 'Gejala uji',
            'incident_date' => '2026-10-20',
        ]);
    }

    public function test_pelapor_sees_only_own_reports(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $lain = User::factory()->create(['role' => 'pelapor']);

        $this->makeReportFor($pelapor, 'Laporan Milik Saya');
        $this->makeReportFor($lain, 'Laporan Milik Orang Lain');

        $this->actingAs($pelapor)->get('/reports')
            ->assertOk()
            ->assertSeeText('Laporan Milik Saya')
            ->assertDontSeeText('Laporan Milik Orang Lain');
    }

    public function test_pelapor_cannot_open_other_pelapor_report_detail(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $lain = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReportFor($lain, 'Laporan Orang Lain');

        $this->actingAs($pelapor)->get('/reports/'.$report->id)->assertForbidden();
    }

    public function test_pelapor_can_open_own_report_detail(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReportFor($pelapor, 'Laporan Saya Detail');

        $this->actingAs($pelapor)->get('/reports/'.$report->id)
            ->assertOk()
            ->assertSeeText('Laporan Saya Detail');
    }

    public function test_pelapor_sees_no_flag_column(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $this->makeReportFor($pelapor, 'Laporan Saya');

        $this->actingAs($pelapor)->get('/reports')
            ->assertOk()
            ->assertDontSeeText('Ditandai');
    }

    public function test_pelapor_status_filter_only_applies_to_own_reports(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $lain = User::factory()->create(['role' => 'pelapor']);

        $this->makeReportFor($pelapor, 'Laporan Saya');
        $other = $this->makeReportFor($lain, 'Laporan Lain');
        Report::whereKey($other->id)->update(['status' => 'closed']);

        $this->actingAs($pelapor)->get('/reports?status=closed')
            ->assertOk()
            ->assertDontSeeText('Laporan Lain');
    }

    public function test_pemantau_still_sees_all_reports(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->makeReportFor($pelapor, 'Laporan Pelapor');

        $this->actingAs($pemantau)->get('/reports')
            ->assertOk()
            ->assertSeeText('Laporan Pelapor');
    }
}
