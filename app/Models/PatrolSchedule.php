<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatrolSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift',
        'start_time',
        'end_time',
        'patrol_date',
        'area',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'patrol_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function patrolLogs(): HasMany
    {
        return $this->hasMany(PatrolLog::class);
    }
}
