<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSubmission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'reporting_year',
        'submitted_at',
        'status',
        'snapshot_json',
        'remarks',
    ];

    protected $casts = [
        'submitted_at'  => 'datetime',
        'snapshot_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
