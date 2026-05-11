<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    
    use HasFactory, Notifiable , HasApiTokens;

    protected $guarded = [];


    public function orders()
    {
        return $this->hasMany(Order::class , 'user_id');
    }   

    public function reviews()
    {
        return $this->hasMany(Review::class , 'user_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class ,  'user_id');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
