<?php

namespace App\Services;

use App\Models\Report;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReportStatusService
{
    /**
     * Peran yang boleh memicu setiap status tujuan.
     */
    private const ROLE_FOR_TARGET = [
        'under_review' => 'pemantau',
        'forwarded' => 'koordinator',
        'closed' => 'koordinator',
    ];

    /**
     * Pemantau meninjau laporan: submitted -> under_review.
     */
    public function review(Report $report, User $actor, ?string $monitorNotes, bool $flagged): Report
    {
        $this->guard($report, 'submitted', 'under_review', $actor);

        $report->monitor_id = $actor->id;
        $report->monitor_notes = $monitorNotes;
        $report->flagged_for_forwarding = $flagged;
        $report->status = 'under_review';
        $report->save();

        return $report;
    }

    /**
     * Koordinator meneruskan laporan: under_review -> forwarded.
     * Hanya laporan yang ditandai pemantau yang boleh diteruskan.
     */
    public function forward(Report $report, User $actor, string $forwardedTo, ?string $coordinatorNotes): Report
    {
        $this->guard($report, 'under_review', 'forwarded', $actor);

        if (! $report->flagged_for_forwarding) {
            throw ValidationException::withMessages([
                'status' => 'Laporan belum ditandai pemantau untuk diteruskan.',
            ]);
        }

        $report->forwarded_to = $forwardedTo;
        $report->coordinator_notes = $coordinatorNotes;
        $report->status = 'forwarded';
        $report->save();

        return $report;
    }

    /**
     * Koordinator menutup laporan: forwarded -> closed.
     */
    public function close(Report $report, User $actor): Report
    {
        $this->guard($report, 'forwarded', 'closed', $actor);

        $report->status = 'closed';
        $report->save();

        return $report;
    }

    /**
     * Pengecekan urutan (status asal harus tepat) dan peran (role harus sesuai).
     */
    private function guard(Report $report, string $from, string $to, User $actor): void
    {
        if ($report->status !== $from) {
            throw ValidationException::withMessages([
                'status' => "Laporan berstatus {$report->status} dan tidak bisa diubah ke {$to}.",
            ]);
        }

        if ($actor->role !== self::ROLE_FOR_TARGET[$to]) {
            throw ValidationException::withMessages([
                'status' => 'Anda tidak memiliki kewenangan untuk perubahan status ini.',
            ]);
        }
    }
}
