<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $table = 'convidados';

    protected $fillable = [
        'id',
        'nome',
        'documento',
        'foto_s3_key'
    ];
}