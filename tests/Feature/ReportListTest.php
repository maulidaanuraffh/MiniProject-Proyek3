<?php

namespace Tests\Feature;

use App\Models\MealDistribution;
use App\Models\Report;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportListTest extends TestCase
{
    use RefreshDatabase;

    private function makeSchoolWithDistribution(string $name): array
    {
        $school = School::create(['name' => $name, 'address' => 'Alamat uji']);
        $distribution = MealDistribution::create([
            'school_id' => $school->id,
            'distribution_date' => '2026-10-20',
        ]);

        return [$school, $distribution];
    }

    private function makeReport(School $school, MealDistribution $distribution, string $title, string $status): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);

        $report = Report::create([
            'user_id' => $pelapor->id,
            'school_id' => $school->id,
            'meal_distribution_id' => $distribution->id,
            'title' => $title,
            'incident_description' => 'Deskripsi uji',
            'symptoms' => 'Gejala uji',
            'incident_date' => '2026-10-20',
        ]);

        // status diatur lewat query builder, tanpa memicu Observer
        Report::whereKey($report->id)->update(['status' => $status]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/reports')->assertRedirect('/login');
    }

    public function test_pelapor_cannot_open_report_list(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);

        $this->actingAs($pelapor)->get('/reports')->assertForbidden();
    }

    public function test_pemantau_sees_reports(): void
    {
        [$school, $distribution] = $this->makeSchoolWithDistribution('SMP Uji');
        $this->makeReport($school, $distribution, 'Laporan Alpha', 'submitted');
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)->get('/reports')
            ->assertOk()
            ->assertSeeText('Laporan Alpha');
    }

    public function test_filter_by_status(): void
    {
        [$school, $distribution] = $this->makeSchoolWithDistribution('SMP Uji');
        $this->makeReport($school, $distribution, 'Laporan Satu', 'submitted');
        $this->makeReport($school, $distribution, 'Laporan Dua', 'under_review');
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($koordinator)->get('/reports?status=under_review')
            ->assertOk()
            ->assertSeeText('Laporan Dua')
            ->assertDontSeeText('Laporan Satu');
    }

    public function test_filter_by_school(): void
    {
        [$schoolA, $distA] = $this->makeSchoolWithDistribution('SMP Satu');
        [$schoolB, $distB] = $this->makeSchoolWithDistribution('SD Dua');
        $this->makeReport($schoolA, $distA, 'Laporan Sekolah A', 'submitted');
        $this->makeReport($schoolB, $distB, 'Laporan Sekolah B', 'submitted');
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)->get('/reports?school_id='.$schoolA->id)
            ->assertOk()
            ->assertSeeText('Laporan Sekolah A')
            ->assertDontSeeText('Laporan Sekolah B');
    }

    public function test_pagination_keeps_status_filter(): void
    {
        [$school, $distribution] = $this->makeSchoolWithDistribution('SMP Uji');

        for ($i = 1; $i <= 12; $i++) {
            $this->makeReport($school, $distribution, 'Laporan Tinjau '.$i, 'under_review');
        }
        for ($i = 1; $i <= 3; $i++) {
            $this->makeReport($school, $distribution, 'Laporan Baru '.$i, 'submitted');
        }
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)->get('/reports?status=under_review&page=2')
            ->assertOk()
            ->assertSeeText('Total: 12 laporan')
            ->assertSeeText('Laporan Tinjau')
            ->assertDontSeeText('Laporan Baru')
            ->assertSee('status=under_review', false);
    }

    public function test_invalid_status_filter_is_rejected(): void
    {
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)->get('/reports?status=ngawur')
            ->assertSessionHasErrors('status');
    }
}
