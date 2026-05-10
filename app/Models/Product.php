<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];


    public function car_detail()
    {
        return $this->hasOne(Car_detail::class , 'product_id');
    }

    public function part_detail()
    {
        return $this->hasOne(Part_detail::class , 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class , 'category_id');
    }
    
    public function images()
    {
        return $this->hasMany(Image::class , 'product_id');
    }   
}
