<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmiTableImage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'table_no',
        'file_path',
        'caption',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
