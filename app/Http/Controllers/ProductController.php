<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //  تابع إنشاء منتج جديد
    //  إنشاء منتج من صلاحيات الأدمن 
    public function store(Request $request)
    {     
    $user = $request->user();
    if($user->role !=='admin')
        {
          return response()->json([
            'success'=>false,
            'message'=>'غير مصرح لك بهذه الصلاحية'
          ], 403);
        }
        //  حقول المنتج الأساسية
     $validated = $request->validate([
        'name'=>'required|string|max:100',
        'price'=>'required',
        'description'=>'nullable|string|max:500',
        'category_id'=>'required|exists:categories,id', 
        'type'=>'required|in:car,part|max:100' ,
        'quantity'=>'required|integer|min:0',

        //  حقول تفاصيل السيارة في حال كان المنتج هو سيارة
        'car_brand'=>'nullable|required_if:type,car|string|max:255',
        'car_model'=>'nullable|required_if:type,car|string|max:255',
        'car_year'=>'nullable|required_if:type,car|integer|min:1900',
        'car_engine_type'=>'nullable|required_if:type,car|string|max:255',
        'car_plate_number'=>'nullable|required_if:type,car|string|max:50',
        'car_color'=>'nullable|required_if:type,car|string|max:255',
        'car_type'=>'nullable|required_if:type,car|string|max:100',

        //  حقول تفاصيل القطعة في حال كان المنتج هو قطعة
        'part_name'=> 'nullable|required_if:type,part|string|max:255',
        'part_condition'=> 'nullable|required_if:type,part|in:new,used',
        'part_warranty'=> 'nullable|string|max:255',
        'part_manufacturer'=> 'nullable|required_if:type,part|string|max:255',
     ]);
      
     $product = Product::create(
        [
            'name'=>$validated['name'],
            'price'=>$validated['price'],
            'description'=>$validated['description'],
            'category_id'=>$validated['category_id'],
            'type'=>$validated['type'],
            'quantity'=>$validated['quantity'],
      
        ]);
        if ($validated['type'] === 'car') {
            $product->car_detail()->create([
                'brand'       => $validated['car_brand'],
                'model'       => $validated['car_model'],
                'year'        => $validated['car_year'],
                'engine_type' => $validated['car_engine_type'],
                'plate_number'=> $validated['car_plate_number'],
                'color'       => $validated['car_color'],
                'type'        => $validated['car_type'], // body type للسيارة
            ]);
        } else {
            $product->part_detail()->create([
                'name'         => $validated['part_name'],
                'condition'    => $validated['part_condition'],
                'warranty'     => $validated['part_warranty'] ?? null,
                'manufacturer' => $validated['part_manufacturer'],
            ]);
        }

        return response()->json(
            [
                'success'=>true,
                'message'=>'تم إنشاء المنتج بنجاح' ,
                'data'=>$product->load('car_detail','part_detail') // تحميل التفاصيل الخاصة بالسيارة أو القطعة
            ] , 201);
    }


}
