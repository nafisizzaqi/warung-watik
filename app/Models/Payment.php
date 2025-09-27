<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'transaction_id', 'payment_type',
        'transaction_status', 'transaction_time', 'gross_amount',
        'fraud_status', 'va_number', 'bill_key', 'biller_code'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

