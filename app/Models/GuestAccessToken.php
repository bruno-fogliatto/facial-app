<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestAccessToken extends Model
{
    use HasFactory;

    protected $table = 'convidados_access_token';

    protected $fillable = [
        'guest_id',
        'token',
        'expires_at',
        'biometry_link'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];
}