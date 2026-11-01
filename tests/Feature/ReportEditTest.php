<?php

namespace Tests\Feature;

use App\Models\MealDistribution;
use App\Models\Report;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportEditTest extends TestCase
{
    use RefreshDatabase;

    // PNG kecil yang valid, dibuat dari byte tetap (tanpa GD)
    private const PNG_BYTES = "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x06\x00\x00\x00\x1f\x15\xc4\x89\x00\x00\x00\rIDATx\x9cc\xf8\x0f\x00\x00\x01\x01\x00\x05\x18\xd8N\x00\x00\x00\x00IEND\xaeB`\x82";

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function makeReport(User $owner, string $status = 'submitted', ?string $evidencePath = null): Report
    {
        $school = School::firstOrCreate(['name' => 'SMP Uji'], ['address' => 'Alamat uji']);
        $distribution = MealDistribution::firstOrCreate(
            ['school_id' => $school->id, 'distribution_date' => '2026-10-20'],
        );

        $report = Report::create([
            'user_id' => $owner->id,
            'school_id' => $school->id,
            'meal_distribution_id' => $distribution->id,
            'title' => 'Judul awal',
            'incident_description' => 'Deskripsi awal',
            'symptoms' => 'Gejala awal',
            'incident_date' => '2026-10-20',
            'evidence_path' => $evidencePath,
            'evidence_original_name' => $evidencePath ? 'lama.png' : null,
        ]);

        // status diatur lewat query builder supaya tidak memicu Observer
        Report::whereKey($report->id)->update(['status' => $status]);

        return $report->fresh();
    }

    private function payload(Report $report, array $extra = []): array
    {
        return array_merge([
            'school_id' => $report->school_id,
            'meal_distribution_id' => $report->meal_distribution_id,
            'title' => 'Judul baru',
            'incident_description' => 'Deskripsi baru',
            'symptoms' => 'Gejala baru',
            'incident_date' => '2026-10-20',
        ], $extra);
    }

    public function test_owner_can_open_edit_form_when_submitted(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReport($owner, 'submitted');

        $this->actingAs($owner)->get('/reports/'.$report->id.'/edit')
            ->assertOk()
            ->assertSee('Judul awal');
    }

    public function test_owner_cannot_open_edit_form_after_submitted(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReport($owner, 'under_review');

        $this->actingAs($owner)->get('/reports/'.$report->id.'/edit')->assertForbidden();
    }

    public function test_other_pelapor_cannot_open_edit_form(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $lain = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReport($owner, 'submitted');

        $this->actingAs($lain)->get('/reports/'.$report->id.'/edit')->assertForbidden();
    }

    public function test_pemantau_cannot_open_edit_form(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $pemantau = User::factory()->create(['role' => 'pemantau']);
        $report = $this->makeReport($owner, 'submitted');

        $this->actingAs($pemantau)->get('/reports/'.$report->id.'/edit')->assertForbidden();
    }

    public function test_owner_can_update_submitted_report(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReport($owner, 'submitted');

        $this->actingAs($owner)
            ->put('/reports/'.$report->id, $this->payload($report))
            ->assertRedirect('/reports/'.$report->id);

        $report->refresh();
        $this->assertSame('Judul baru', $report->title);
        $this->assertSame('submitted', $report->status);
    }

    public function test_update_is_rejected_after_submitted(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReport($owner, 'under_review');

        $this->actingAs($owner)
            ->put('/reports/'.$report->id, $this->payload($report))
            ->assertForbidden();

        $this->assertSame('Judul awal', $report->fresh()->title);
    }

    public function test_other_pelapor_cannot_update_report(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $lain = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReport($owner, 'submitted');

        $this->actingAs($lain)
            ->put('/reports/'.$report->id, $this->payload($report))
            ->assertForbidden();

        $this->assertSame('Judul awal', $report->fresh()->title);
    }

    public function test_update_rejects_distribution_from_other_school(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);
        $report = $this->makeReport($owner, 'submitted');

        $schoolLain = School::create(['name' => 'SD Lain', 'address' => 'Alamat lain']);
        $distribusiLain = MealDistribution::create([
            'school_id' => $schoolLain->id,
            'distribution_date' => '2026-10-21',
        ]);

        // sekolah dipilih: SMP Uji, distribusi milik SD Lain
        $this->actingAs($owner)
            ->put('/reports/'.$report->id, $this->payload($report, [
                'meal_distribution_id' => $distribusiLain->id,
            ]))
            ->assertSessionHasErrors('meal_distribution_id');

        $this->assertSame('Judul awal', $report->fresh()->title);
    }

    public function test_replacing_evidence_removes_old_file(): void
    {
        $owner = User::factory()->create(['role' => 'pelapor']);

        Storage::disk('local')->put('evidence/lama.png', self::PNG_BYTES);
        $report = $this->makeReport($owner, 'submitted', 'evidence/lama.png');

        $baru = UploadedFile::fake()->createWithContent('baru.png', self::PNG_BYTES);

        $this->actingAs($owner)
            ->put('/reports/'.$report->id, $this->payload($report, ['evidence' => $baru]))
            ->assertRedirect();

        $report->refresh();
        Storage::disk('local')->assertMissing('evidence/lama.png');
        Storage::disk('local')->assertExists($report->evidence_path);
        $this->assertSame('baru.png', $report->evidence_original_name);
    }
}
