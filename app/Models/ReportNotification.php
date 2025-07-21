<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'report_name',
        'report_types',
        'format',
        'file_path',
        'file_name',
        'file_size',
        'status',
        'error_message',
        'generated_at',
        'is_read'
    ];

    protected $casts = [
        'report_types' => 'array',
        'generated_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) return 'Unknown';

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get report types as formatted string
     */
    public function getFormattedReportTypesAttribute(): string
    {
        if (!$this->report_types) return 'No reports';

        return collect($this->report_types)
            ->map(fn($type) => ucfirst(str_replace('_', ' ', $type)))
            ->join(', ');
    }
}
