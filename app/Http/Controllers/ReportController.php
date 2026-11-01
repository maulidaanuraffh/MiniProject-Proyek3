<?php

namespace App\Http\Controllers;

use App\Models\MealDistribution;
use App\Models\Report;
use App\Models\School;
use App\Services\ReportStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReportController extends Controller
{
    private const STATUSES = ['submitted', 'under_review', 'forwarded', 'closed'];

    private const PER_PAGE = 10;

    private const FORWARD_TARGETS = ['Sekolah', 'SPPG', 'Fasilitas kesehatan', 'Instansi terkait lainnya'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:'.implode(',', self::STATUSES)],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
        ]);

        $reports = Report::with('school')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['school_id'] ?? null, fn ($query, $schoolId) => $query->where('school_id', $schoolId))
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('reports.index', [
            'reports' => $reports,
            'schools' => School::orderBy('name')->get(),
            'statuses' => self::STATUSES,
            'filters' => $filters,
        ]);
    }

    public function show(Report $report): View
    {
        Gate::authorize('view', $report);

        // Nama pelapor sengaja tidak dimuat dan tidak ditampilkan (spesifikasi bagian 2)
        return view('reports.show', [
            'report' => $report->load(['school', 'mealDistribution', 'monitor']),
            'forwardTargets' => self::FORWARD_TARGETS,
        ]);
    }

    public function review(Request $request, Report $report, ReportStatusService $statusService): RedirectResponse
    {
        $validated = $request->validate([
            'monitor_notes' => ['nullable', 'string', 'max:2000'],
            'flagged_for_forwarding' => ['nullable', 'boolean'],
        ]);

        $statusService->review(
            $report,
            $request->user(),
            $validated['monitor_notes'] ?? null,
            (bool) ($validated['flagged_for_forwarding'] ?? false),
        );

        return redirect()
            ->route('reports.show', $report)
            ->with('status', 'Laporan berhasil ditinjau dan statusnya menjadi under_review.');
    }

    public function forward(Request $request, Report $report, ReportStatusService $statusService): RedirectResponse
    {
        $validated = $request->validate([
            'forwarded_to' => ['required', 'string', 'in:'.implode(',', self::FORWARD_TARGETS)],
            'coordinator_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $statusService->forward(
            $report,
            $request->user(),
            $validated['forwarded_to'],
            $validated['coordinator_notes'] ?? null,
        );

        return redirect()
            ->route('reports.show', $report)
            ->with('status', 'Laporan berhasil diteruskan ke '.$validated['forwarded_to'].'.');
    }

    public function close(Request $request, Report $report, ReportStatusService $statusService): RedirectResponse
    {
        $statusService->close($report, $request->user());

        return redirect()
            ->route('reports.show', $report)
            ->with('status', 'Laporan berhasil ditutup.');
    }

    public function create(): View
    {
        return view('reports.create', [
            'schools' => School::orderBy('name')->get(),
            'distributions' => MealDistribution::with('school')
                ->orderByDesc('distribution_date')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'meal_distribution_id' => ['required', 'integer', 'exists:meal_distributions,id'],
            'title' => ['required', 'string', 'max:150'],
            'incident_description' => ['required', 'string', 'max:5000'],
            'symptoms' => ['required', 'string', 'max:5000'],
            'affected_count' => ['nullable', 'integer', 'min:1'],
            'incident_date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        // BR-02: distribusi harus milik sekolah yang dipilih
        $distribution = MealDistribution::findOrFail($validated['meal_distribution_id']);

        if ((int) $distribution->school_id !== (int) $validated['school_id']) {
            throw ValidationException::withMessages([
                'meal_distribution_id' => 'Distribusi yang dipilih bukan milik sekolah tersebut.',
            ]);
        }

        $report = Report::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('reports.create')
            ->with('status', 'Laporan "'.$report->title.'" berhasil dikirim dan tercatat dengan status '.$report->status.'.');
    }
}
