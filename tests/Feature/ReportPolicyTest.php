<?php

namespace Tests\Feature;

use App\Models\MealDistribution;
use App\Models\Report;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function makeReport(User $owner): Report
    {
        $school = School::create(['name' => 'SMP Uji', 'address' => 'Alamat uji']);
        $distribution = MealDistribution::create([
            'school_id' => $school->id,
            'distribution_date' => '2026-10-20',
        ]);

        $report = Report::create([
            'user_id' => $owner->id,
            'school_id' => $school->id,
            'meal_distribution_id' => $distribution->id,
            'title' => 'Laporan uji',
            'incident_description' => 'Deskripsi uji',
            'symptoms' => 'Gejala uji',
            'incident_date' => '2026-10-20',
        ]);

        // Baca ulang dari database supaya status default 'submitted' ikut terbaca
        return $report->fresh();
    }

    public function test_pelapor_can_view_own_report(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReport($owner);

        $this->assertTrue($owner->can('view', $report));
    }

    public function test_pelapor_cannot_view_other_report(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $other = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReport($owner);

        $this->assertFalse($other->can('view', $report));
    }

    public function test_pemantau_and_koordinator_can_view_any_report(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReport($owner);

        $this->assertTrue(User::factory()->create(['role' => 'pemantau'])->can('view', $report));
        $this->assertTrue(User::factory()->create(['role' => 'koordinator'])->can('view', $report));
    }

    public function test_pelapor_can_update_own_submitted_report(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReport($owner);

        $this->assertTrue($owner->can('update', $report));
    }

    public function test_pelapor_cannot_update_report_after_submitted(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReport($owner);

        // status tidak ada di $fillable, jadi diubah langsung lalu disimpan
        $report->status = 'under_review';
        $report->save();

        $this->assertFalse($owner->can('update', $report));
    }

    public function test_pelapor_cannot_update_other_report(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $other = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReport($owner);

        $this->assertFalse($other->can('update', $report));
    }
}
