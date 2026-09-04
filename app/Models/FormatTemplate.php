<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormatTemplate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'year',
        'table_no',
        'title',
        'subtitle',
        'section',
        'is_required',
        'is_locked',
        'columns_json',
        'sort_order',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_required'  => 'boolean',
        'is_locked'    => 'boolean',
        'columns_json' => 'array',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
