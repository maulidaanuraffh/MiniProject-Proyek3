<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealDistribution extends Model
{
    use HasFactory;

    protected $fillable = ['school_id', 'distribution_date', 'description'];

    protected function casts(): array
    {
        return [
            'distribution_date' => 'date',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
