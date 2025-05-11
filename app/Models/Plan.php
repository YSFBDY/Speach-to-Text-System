<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'plans';
    protected $primaryKey = 'plan_id';

    protected $fillable = [
        'plan_name',
        'plan_price',
        'plan_price_cents',
        'plan_description',
        "plan_period",
        'plan_transcription_limit',
        'plan_translation_limit',
    ];
   
    public function getPlanPriceAttribute($value)
    {
        return intval($value). " " .'EGP' ;  // Formats the price to 2 decimal places
    }



}
