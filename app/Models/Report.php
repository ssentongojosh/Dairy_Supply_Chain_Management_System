<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    //
    protected $table = 'reports'; // Specify the table name if it differs from the default
    protected $fillable = [
        'user_id',
        'report_name',
        'frequency',
        'report_types',
        'format',
        'file_path',
        'file_name',
        'file_size',
        'report_start_date',
        'report_end_date',
        'generated_at',
        'status',
        'error_message',
        'download_count',
        'expires_at'
    ];
    protected $casts = [
        'report_types' => 'array', // Cast JSON to array
        'generated_at' => 'datetime',
        'expires_at' => 'datetime',
        'report_start_date' => 'date',
        'report_end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
