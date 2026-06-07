<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PatrolLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'patrol_schedule_id',
        'user_id',
        'logged_at',
        'location_checked',
        'condition',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
        ];
    }

    public function patrolSchedule(): BelongsTo
    {
        return $this->belongsTo(PatrolSchedule::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
