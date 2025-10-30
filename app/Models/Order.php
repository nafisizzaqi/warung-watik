<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
    'customer_id',
    'order_number',
    'queue_number',
    'status',
    'total_amount',
    'shipping_cost',
    'grand_total',
    'payment_method',
    'shipping_address',
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasOne(Payment::class, 'order_id');
    }

    public function shipments()
    {
        return $this->hasOne(Shipment::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
