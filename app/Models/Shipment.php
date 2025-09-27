<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = ['order_id', 'courier', 'service', 'cost', 'etd', 'tracking_number', 'status'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
