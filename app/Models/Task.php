<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'description',
        'due_date',
        'priority',
        'status',
        'related_id',
        'related_type',
        'assigned_at',
        'completed_at',
        'wholesaler_id',
        'forecast_start_date',
        'forecast_end_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'forecast_start_date' => 'datetime', // NEW: Cast to datetime
        'forecast_end_date' => 'datetime',
    ];

    /**
     * Get the user that owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent model that the task belongs to (polymorphic).
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    // You might want to define constants for priority and status
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const STATUS_PENDING = 'pending'; // Created, awaiting assignment
    public const STATUS_ASSIGNED = 'assigned'; // Assigned, not yet started
    public const STATUS_IN_PROGRESS = 'in_progress'; // Being worked on
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed'; // Task could not be completed (e.g., delivery failed)
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_SUGGESTED = 'suggested';
    public const STATUS_FOR_INSPECTION = 'for_inspection';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

}
