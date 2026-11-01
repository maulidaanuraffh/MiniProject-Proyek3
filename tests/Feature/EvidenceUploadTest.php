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

class EvidenceUploadTest extends TestCase
{
    use RefreshDatabase;

    // PNG paling kecil yang valid, dibuat dari byte tetap (tanpa GD)
    private const PNG_BYTES = "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x06\x00\x00\x00\x1f\x15\xc4\x89\x00\x00\x00\rIDATx\x9cc\xf8\x0f\x00\x00\x01\x01\x00\x05\x18\xd8N\x00\x00\x00\x00IEND\xaeB`\x82";

    protected function setUp(): void
    {
        parent::setUp();

        // Penyimpanan palsu supaya file uji tidak masuk ke storage asli
        Storage::fake('local');
    }

    private function makePng(string $name = 'bukti.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, self::PNG_BYTES);
    }

    private function makeSchoolAndDistribution(): array
    {
        $school = School::create(['name' => 'SMP Uji', 'address' => 'Alamat uji']);
        $distribution = MealDistribution::create([
            'school_id' => $school->id,
            'distribution_date' => '2026-10-20',
        ]);

        return [$school, $distribution];
    }

    private function payload(School $school, MealDistribution $distribution, array $extra = []): array
    {
        return array_merge([
            'school_id' => $school->id,
            'meal_distribution_id' => $distribution->id,
            'title' => 'Keluhan dengan bukti',
            'incident_description' => 'Deskripsi uji',
            'symptoms' => 'Mual',
            'incident_date' => '2026-10-20',
        ], $extra);
    }

    private function submitReport(User $pelapor, array $extra = []): void
    {
        [$school, $distribution] = $this->makeSchoolAndDistribution();

        $this->actingAs($pelapor)
            ->post('/reports', $this->payload($school, $distribution, $extra))
            ->assertRedirect('/reports/create');
    }

    public function test_valid_png_is_stored_and_recorded(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);

        $this->submitReport($pelapor, ['evidence' => $this->makePng()]);

        $report = Report::firstOrFail();
        $this->assertSame('bukti.png', $report->evidence_original_name);
        Storage::disk('local')->assertExists($report->evidence_path);
    }

    public function test_valid_pdf_is_accepted(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $pdf = UploadedFile::fake()->createWithContent('bukti.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF");

        $this->submitReport($pelapor, ['evidence' => $pdf]);

        $this->assertDatabaseCount('reports', 1);
        Storage::disk('local')->assertExists(Report::firstOrFail()->evidence_path);
    }

    public function test_disallowed_file_type_is_rejected(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        [$school, $distribution] = $this->makeSchoolAndDistribution();

        $this->actingAs($pelapor)
            ->post('/reports', $this->payload($school, $distribution, [
                'evidence' => UploadedFile::fake()->create('virus.exe', 10),
            ]))
            ->assertSessionHasErrors('evidence');

        $this->assertDatabaseCount('reports', 0);
        $this->assertEmpty(Storage::disk('local')->allFiles('evidence'));
    }

    public function test_file_over_two_megabytes_is_rejected(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        [$school, $distribution] = $this->makeSchoolAndDistribution();

        // 3 MB, di atas batas 2048 KB
        $besar = UploadedFile::fake()->create('besar.pdf', 3072, 'application/pdf');

        $this->actingAs($pelapor)
            ->post('/reports', $this->payload($school, $distribution, [
                'evidence' => $besar,
            ]))
            ->assertSessionHasErrors('evidence');

        $this->assertDatabaseCount('reports', 0);
        $this->assertEmpty(Storage::disk('local')->allFiles('evidence'));
    }

    public function test_owner_can_download_evidence(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $this->submitReport($pelapor, ['evidence' => $this->makePng()]);

        $report = Report::firstOrFail();

        $this->actingAs($pelapor)
            ->get('/reports/'.$report->id.'/evidence')
            ->assertOk();
    }

    public function test_other_pelapor_cannot_download_evidence(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $this->submitReport($pelapor, ['evidence' => $this->makePng()]);

        $report = Report::firstOrFail();
        $pelaporLain = User::factory()->create(['role' => 'pelapor']);

        $this->actingAs($pelaporLain)
            ->get('/reports/'.$report->id.'/evidence')
            ->assertForbidden();
    }

    public function test_pemantau_can_download_evidence(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $this->submitReport($pelapor, ['evidence' => $this->makePng()]);

        $report = Report::firstOrFail();
        $pemantau = User::factory()->create(['role' => 'pemantau']);

        $this->actingAs($pemantau)
            ->get('/reports/'.$report->id.'/evidence')
            ->assertOk();
    }

    public function test_guest_is_redirected_from_evidence(): void
    {
        $pelapor = User::factory()->create(['role' => 'pelapor']);
        $this->submitReport($pelapor, ['evidence' => $this->makePng()]);

        $report = Report::firstOrFail();
        auth()->guard()->logout();

        $this->get('/reports/'.$report->id.'/evidence')->assertRedirect('/login');
    }
}
