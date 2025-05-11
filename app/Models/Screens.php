<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Screens extends Model
{
    protected $table = 'screens';
    protected $primaryKey = 'screen_id';

    protected $fillable = [
        'screen_name',
        'feedback',
        'user_id',
    ];
}
