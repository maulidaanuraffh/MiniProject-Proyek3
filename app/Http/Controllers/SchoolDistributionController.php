<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\JsonResponse;

class SchoolDistributionController extends Controller
{
    public function index(School $school): JsonResponse
    {
        $distributions = $school->mealDistributions()
            ->orderByDesc('distribution_date')
            ->get(['id', 'distribution_date']);

        return response()->json(
            $distributions->map(fn ($distribution) => [
                'id' => $distribution->id,
                'label' => $distribution->distribution_date->format('d M Y'),
            ])->values()
        );
    }
}
