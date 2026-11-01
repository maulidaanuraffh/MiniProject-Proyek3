<?php

namespace App\Http\Controllers;

use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DataManagementController extends Controller
{
    public function schoolsIndex(): View
    {
        return view('manage.schools', [
            'schools' => School::withCount('mealDistributions')->orderBy('name')->get(),
        ]);
    }

    public function storeSchool(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:schools,name'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        School::create($validated);

        return redirect()
            ->route('manage.schools.index')
            ->with('status', 'Sekolah "'.$validated['name'].'" berhasil ditambahkan.');
    }

    public function distributionsIndex(School $school): View
    {
        return view('manage.distributions', [
            'school' => $school,
            'distributions' => $school->mealDistributions()
                ->orderByDesc('distribution_date')
                ->get(),
        ]);
    }

    public function storeDistribution(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'distribution_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        // Ubah ke tanggal murni (YYYY-MM-DD) supaya cocok dengan kolom date
        $date = Carbon::parse($validated['distribution_date'])->toDateString();

        $sudahAda = $school->mealDistributions()
            ->whereDate('distribution_date', $date)
            ->exists();

        if ($sudahAda) {
            throw ValidationException::withMessages([
                'distribution_date' => 'Distribusi untuk tanggal ini sudah ada di sekolah tersebut.',
            ]);
        }

        $school->mealDistributions()->create([
            'distribution_date' => $date,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('manage.distributions.index', $school)
            ->with('status', 'Distribusi tanggal '.$date.' berhasil ditambahkan.');
    }
}
