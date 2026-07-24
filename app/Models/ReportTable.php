<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportTable extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'reporting_year',
        'table_no',
        'meta_json',
        'rows_json',
        'status',
        'updated_at',
    ];

    protected $casts = [
        'meta_json'  => 'array',
        'rows_json'  => 'array',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
