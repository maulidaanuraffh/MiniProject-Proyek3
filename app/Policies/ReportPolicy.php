<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /**
     * Pelapor hanya bisa melihat laporannya sendiri.
     * Pemantau dan koordinator bisa melihat semua laporan.
     */
    public function view(User $user, Report $report): bool
    {
        if (in_array($user->role, ['pemantau', 'koordinator'], true)) {
            return true;
        }

        return $user->id === $report->user_id;
    }

    /**
     * Pelapor hanya bisa mengedit laporannya sendiri, dan hanya saat status masih submitted (BR-01).
     */
    public function update(User $user, Report $report): bool
    {
        return $user->role === 'pelapor'
            && $user->id === $report->user_id
            && $report->status === 'submitted';
    }
}
