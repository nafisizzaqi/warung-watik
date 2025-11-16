<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentService extends Model
{
    protected $fillable = [
        'courier',
        'code',
        'label',
        'cost',
    ];
}
    