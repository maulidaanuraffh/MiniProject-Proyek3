<?php

namespace App\Http\Controllers;

use App\Models\MealDistribution;
use App\Models\Report;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReportController extends Controller
{
    private const STATUSES = ['submitted', 'under_review', 'forwarded', 'closed'];

    private const PER_PAGE = 10;

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
