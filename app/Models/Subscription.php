<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'subscriptions';

    protected $primaryKey = 'subscription_id';

    protected $fillable = [
        'user_id',
        'plan_id',
        'remain_transcription_limit',
        'remain_translation_limit',
        'start_date',
        'end_date',
        'subscription_status',
    ];

    public $timestamps = false;

}
