<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'image',
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];  

    public function getJWTIndentifier()
    {
        return $this->getKey();
    }   

    public function getJWTCustomClaims()
    {
        return [];
    }   
}

