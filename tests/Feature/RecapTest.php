<?php

namespace Tests\Feature;

use App\Models\MealDistribution;
use App\Models\Report;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecapTest extends TestCase
{
    use RefreshDatabase;

    private function makeReport(School $school, string $date, string $status): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $distribution = MealDistribution::create([
            'school_id' => $school->id,
            'distribution_date' => $date,
        ]);

        $report = Report::create([
            'user_id' => $pelapor->id,
            'school_id' => $school->id,
            'meal_distribution_id' => $distribution->id,
            'title' => 'Laporan rekap',
            'incident_description' => 'Deskripsi uji',
            'symptoms' => 'Gejala uji',
            'incident_date' => $date,
        ]);

        // status diatur lewat query builder supaya tidak memicu Observer
        Report::whereKey($report->id)->update(['status' => $status]);
    }

    private function seedData(): array
    {
        $schoolA = School::create(['name' => 'SMP A', 'address' => 'Alamat A']);
        $schoolB = School::create(['name' => 'SMP B', 'address' => 'Alamat B']);

        $this->makeReport($schoolA, '2026-10-20', 'submitted');
        $this->makeReport($schoolA, '2026-10-20', 'submitted');
        $this->makeReport($schoolA, '2026-10-20', 'under_review');
        $this->makeReport($schoolA, '2026-10-20', 'forwarded');
        $this->makeReport($schoolB, '2026-10-20', 'closed');

        return [$schoolA, $schoolB];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/rekap')->assertRedirect('/login');
    }

    public function test_pelapor_cannot_open_recap(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);

        $this->actingAs($pelapor)->get('/rekap')->assertForbidden();
    }

    public function test_koordinator_can_open_recap(): void
    {
        $koordinator = User::factory()->create(['role' => 'koordinator']);

        $this->actingAs($koordinator)->get('/rekap')->assertOk();
    }

    public function test_summary_counts_are_correct(): void
    {
        $this->seedData();
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $response = $this->actingAs($pemantau)->get('/rekap');

        $response->assertOk();
        $this->assertSame(
            ['total' => 5, 'diproses' => 3, 'selesai' => 2],
            $response->viewData('summary'),
        );
    }

    public function test_per_school_counts_are_correct(): void
    {
        $this->seedData();
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $rows = $this->actingAs($pemantau)->get('/rekap')
            ->viewData('perSchool')
            ->keyBy('school_name');

        $this->assertEquals(4, $rows['SMP A']->total);
        $this->assertEquals(2, $rows['SMP A']->submitted);
        $this->assertEquals(1, $rows['SMP A']->under_review);
        $this->assertEquals(1, $rows['SMP A']->forwarded);
        $this->assertEquals(0, $rows['SMP A']->closed);

        $this->assertEquals(1, $rows['SMP B']->total);
        $this->assertEquals(1, $rows['SMP B']->closed);
    }

    public function test_date_filter_limits_summary_and_table(): void
    {
        $schoolA = School::create(['name' => 'SMP A', 'address' => 'Alamat A']);
        $this->makeReport($schoolA, '2026-10-01', 'submitted'); // di luar rentang
        $this->makeReport($schoolA, '2026-10-20', 'submitted'); // di dalam rentang
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $response = $this->actingAs($pemantau)->get('/rekap?from=2026-10-15&to=2026-10-31');

        $response->assertOk();
        $this->assertSame(1, $response->viewData('summary')['total']);
        $this->assertSame(1, $response->viewData('perSchool')->first()->total);
    }

    public function test_end_date_before_start_date_is_rejected(): void
    {
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)
            ->get('/rekap?from=2026-10-20&to=2026-10-01')
            ->assertSessionHasErrors('to');
    }

    public function test_total_equals_sum_of_per_school_totals(): void
    {
        $this->seedData();
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $response = $this->actingAs($pemantau)->get('/rekap');

        $sumPerSchool = $response->viewData('perSchool')->sum('total');
        $this->assertSame($response->viewData('summary')['total'], (int) $sumPerSchool);
    }
}
