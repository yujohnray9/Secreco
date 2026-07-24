<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingRegistration extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'institution',
        'designation',
        'otp',
        'otp_expires_at',
        'created_at',
        'verified',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
        'created_at'     => 'datetime',
        'verified'       => 'boolean',
    ];
}
