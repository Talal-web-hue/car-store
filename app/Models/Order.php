<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];


    public function invoice()
    {
        return $this->hasOne(Invoice::class , 'order_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class , 'order_id');
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class , 'order_id');
    }

    public function order_item()
    {
        return $this->hasMany(Order_item::class , 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
}
