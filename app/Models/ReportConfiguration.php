<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import for the relationship

class ReportConfiguration extends Model
{
    //
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'report_configurations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'frequency',
        'send_time',
        'day_of_week',
        'day_of_month',
        'report_types',         // Will be cast to array/JSON
        'format',
        'notification_channels',// Will be cast to array/JSON
        'is_active',
        'last_generated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'report_types' => 'array',
        'notification_channels' => 'array',
        'is_active' => 'boolean',
        'last_generated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the report configuration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
