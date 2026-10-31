<?php

namespace Database\Seeders;

use App\Models\MealDistribution;
use App\Models\Report;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoReportSeeder extends Seeder
{
    public function run(): void
    {
        $pelapor = User::where('email', 'pelapor@demo.test')->firstOrFail();
        $pemantau = User::where('email', 'pemantau@demo.test')->firstOrFail();

        $schools = School::orderBy('id')->get();

        foreach ($schools as $school) {
            foreach (['2026-10-20', '2026-10-27'] as $date) {
                $distribution = MealDistribution::updateOrCreate(
                    ['school_id' => $school->id, 'distribution_date' => $date],
                    ['description' => 'Menu makan siang dummy'],
                );

                $this->createReport(
                    $pelapor,
                    $school,
                    $distribution,
                    'Keluhan dummy ' . $school->name . ' ' . $date,
                    'submitted',
                );
            }
        }

        $firstDistribution = MealDistribution::where('school_id', $schools->first()->id)
            ->orderBy('distribution_date')
            ->firstOrFail();

        $this->setStatus($firstDistribution, 'Laporan dummy under_review', 'under_review', $pemantau);
        $this->setStatus($firstDistribution, 'Laporan dummy forwarded', 'forwarded', $pemantau);
        $this->setStatus($firstDistribution, 'Laporan dummy closed', 'closed', $pemantau);
    }

    private function createReport(User $pelapor, School $school, MealDistribution $distribution, string $title, string $status): Report
    {
        return Report::updateOrCreate(
            ['title' => $title],
            [
                'user_id' => $pelapor->id,
                'school_id' => $school->id,
                'meal_distribution_id' => $distribution->id,
                'incident_description' => 'Deskripsi dummy untuk pengujian.',
                'symptoms' => 'Mual dan sakit perut (data dummy).',
                'affected_count' => 3,
                'incident_date' => $distribution->distribution_date,
                'status' => $status,
            ],
        );
    }

    private function setStatus(MealDistribution $distribution, string $title, string $status, User $pemantau): void
    {
        Report::updateOrCreate(
            ['title' => $title],
            [
                'user_id' => User::where('email', 'pelapor@demo.test')->value('id'),
                'school_id' => $distribution->school_id,
                'meal_distribution_id' => $distribution->id,
                'incident_description' => 'Deskripsi dummy untuk pengujian.',
                'symptoms' => 'Mual dan sakit perut (data dummy).',
                'affected_count' => 3,
                'incident_date' => $distribution->distribution_date,
                'status' => $status,
                'monitor_id' => $pemantau->id,
                'monitor_notes' => 'Catatan dummy dari pemantau.',
            ],
        );
    }
}
