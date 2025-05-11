<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Otp extends Model
{
    use HasFactory;
    protected $table = 'otps';
    protected $primaryKey = 'otp_id';

    protected $fillable = [
        'email',
        'otp',
        'expires_at'
    ];
    
}
