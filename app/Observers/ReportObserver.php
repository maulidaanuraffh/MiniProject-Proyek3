<?php

namespace App\Observers;

use App\Models\Report;
use App\Models\ReportStatusLog;
use Illuminate\Support\Facades\Auth;

class ReportObserver
{
    public function updated(Report $report): void
    {
        if (! $report->wasChanged('status')) {
            return;
        }

        ReportStatusLog::create([
            'report_id' => $report->id,
            'changed_by_user_id' => Auth::id(),
            'old_status' => $report->getOriginal('status'),
            'new_status' => $report->status,
        ]);
    }
}
