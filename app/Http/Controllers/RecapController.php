<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RecapController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        // Tanggal dalam format YYYY-MM-DD bisa dibandingkan sebagai teks
        if (! empty($filters['from']) && ! empty($filters['to']) && $filters['to'] < $filters['from']) {
            throw ValidationException::withMessages([
                'to' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
            ]);
        }

        // Filter rentang tanggal kejadian, dipakai bersama untuk ringkasan dan tabel
        $applyDateRange = fn ($query) => $query
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->where('reports.incident_date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->where('reports.incident_date', '<=', $to));

        $summary = [
            'total' => $applyDateRange(Report::query())->count(),
            'diproses' => $applyDateRange(Report::query())
                ->whereIn('reports.status', ['submitted', 'under_review'])
                ->count(),
            'selesai' => $applyDateRange(Report::query())
                ->whereIn('reports.status', ['forwarded', 'closed'])
                ->count(),
        ];

        // COUNT(CASE ...) mengembalikan integer dan mengabaikan NULL, sehingga aman di MariaDB dan SQLite
        $perSchool = $applyDateRange(
            Report::query()->join('schools', 'schools.id', '=', 'reports.school_id')
        )
            ->selectRaw('schools.name as school_name')
            ->selectRaw('COUNT(reports.id) as total')
            ->selectRaw("COUNT(CASE WHEN reports.status = 'submitted' THEN 1 END) as submitted")
            ->selectRaw("COUNT(CASE WHEN reports.status = 'under_review' THEN 1 END) as under_review")
            ->selectRaw("COUNT(CASE WHEN reports.status = 'forwarded' THEN 1 END) as forwarded")
            ->selectRaw("COUNT(CASE WHEN reports.status = 'closed' THEN 1 END) as closed")
            ->groupBy('schools.id', 'schools.name')
            ->orderBy('schools.name')
            ->get();

        return view('recap.index', [
            'filters' => $filters,
            'summary' => $summary,
            'perSchool' => $perSchool,
        ]);
    }
}
