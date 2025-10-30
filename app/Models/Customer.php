<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Customer extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'image',
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // ---- JWTSubject methods ----
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getImageUrlAttribute()
{
    if ($this->image) {
        return asset('storage/' . $this->image);
    }

    // Kalau image kosong, generate avatar via UI Avatars (nggak perlu simpan file)
    return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random&color=fff';
}

}
