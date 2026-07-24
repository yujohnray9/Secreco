<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cmi_user_id',
        'pta_user_id',
        'reporting_year',
        'table_no',
        'reason',
        'status',
        'created_at',
        'resolved_at',
    ];

    protected $casts = [
        'created_at'  => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function cmiUser()
    {
        return $this->belongsTo(User::class, 'cmi_user_id');
    }

    public function ptaUser()
    {
        return $this->belongsTo(User::class, 'pta_user_id');
    }
}
