<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    /**
     * Status dan kolom hasil peninjauan tidak masuk sini.
     * Perubahannya hanya boleh lewat service yang mengecek aturan alur.
     */
    protected $fillable = [
        'user_id',
        'school_id',
        'meal_distribution_id',
        'title',
        'incident_description',
        'symptoms',
        'affected_count',
        'incident_date',
        'evidence_path',
        'evidence_original_name',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'flagged_for_forwarding' => 'boolean',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'monitor_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function mealDistribution(): BelongsTo
    {
        return $this->belongsTo(MealDistribution::class);
    }
}
