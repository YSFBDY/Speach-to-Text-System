<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'payment_currency',
        'payment_status',
        'order_id',
        'merchant_order_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'payment_amount',
        'payment_amount_cent',
        'user_id',
        'plan_id'
    ];
}
