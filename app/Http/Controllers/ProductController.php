<?php

namespace App\Http\Controllers;

use App\Models\Category;
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

 
    // تابع تحديث المنتج
    //  تحديث المنتج من صلاحيات الأدمن أيضا
    public function update(Request $request , $productId)
    {
     $user = $request->user();
     if($user->role !=='admin')
        {
          return response()->json([
            'success'=>false,
            'message'=>'غير مصرح لك بهذه الصلاحية'
          ], 403);
        }
        $validated = $request->validate([
            'name'=>'sometimes|required|string|max:100',
            'price'=>'sometimes|required',
            'description'=>'nullable|string|max:500',
            'category_id'=>'sometimes|required|exists:categories,id', 
            'type'=>'sometimes|required|in:car,part|max:100' ,
            'quantity'=>'sometimes|required|integer|min:0',

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
         $product = Product::find($productId);
         if(!$product){
             return response()->json([
                'success'=>false,
                'message'=>'المنتج غير موجود او أنه محذوف مسبقا'
             ]);
         }
         $product->update($validated);
         if ($product->type === 'car') {
             $product->car_detail()->updateOrCreate(
                 ['product_id' => $product->id],
                 [
                     'brand'       => $validated['car_brand'] ?? $product->car_detail->brand,
                     'model'       => $validated['car_model'] ?? $product->car_detail->model,
                     'year'        => $validated['car_year'] ?? $product->car_detail->year,
                     'engine_type' => $validated['car_engine_type'] ?? $product->car_detail->engine_type,
                     'plate_number'=> $validated['car_plate_number'] ?? $product->car_detail->plate_number,
                     'color'       => $validated['car_color'] ?? $product->car_detail->color,
                     'type'        => $validated['car_type'] ?? $product->car_detail->type,
                 ]);
    }   
}
 
//    حذف منتج و هي من صلاحيات الأدمن 
   public function destroy(Request $request , $productId)
   {
    $user = $request->user();
    if($user->role !=='admin')
        {
          return response()->json(
            [
                'success'=>false ,
                'message'=>'غير مصرح لك بحذف أي منتج'
            ] , 403);
        }
        $product = Product::find($productId);
        if(!$productId)
            {
                       return response()->json(
            [
                'success'=>false ,
                'message'=>'المنتج الذي تريد حذفه غير موجود أو أنك قمت بحذفه مسبقا'
            ] , 403);
            }
            //  نضع شرط أنه إذا كان للمنتج طلبات مرتبطة , لا يمكننا حذفه عندها 
            if($product->order_items()->exists())
                {
                return response()->json([
                    'success'=>false,
                    'message'=>'لا يمكنك حذف منتج له طلبات متعلقة به'
                ] , 422);
                }
            $product->forceDelete();
            return response()->json(
                [
                    'success'=>true , 
                    'message'=>'تم حذف المنتج بنجاح'
                ] , 200);
   }

//     تابع جلب المنتجات و هو متاح للجميع من اجل مشاهدة جميع المنتجات المتوفرة لدينا
 public function index()
 {
    $products = Product::with('car_detail','part_detail')->paginate(10);
    return response()->json([
        'success'=>true,
        'message'=>'تم جلب المنتجات بنجاح',
        'data'=>$products // إضافة التصفح مع 10 منتجات في كل صفحة
    ] , 200);
 }

//  تابع البحث عن منتج حسب اسمه أو سعره أو وصفه
  public function search(Request $request)
  {
   if(!$request->filled('name') && !$request->filled('price') && !$request->filled('description'))
    {
     return response()->json(
        [
            'success'=>false,
            'message'=>'يرجى إدخال قيمة واحدة على الأقل من أجل إرجاع نتيجة للبحث'
        ]  , 422);
    }
    $query = Product::query();
    if($request->filled('name'))
        { 
            $query->where('name', 'like','%' . $request->name . '%');

        }
        if($request->filled('price'))
        { 
            $query->where('price', 'like','%' . $request->price . '%');

        }
        if($request->filled('description'))
        { 
            $query->where('description', 'like','%' . $request->description . '%');

        }   
    $products = $query->with([
        'Category'=>fn($q)=>$q->select('id' , 'name')
    ])->paginate(10); 

    return response()->json([
        'success'=>true,
        'message'=>'تم جلب المنتجات بنجاح',
        'عدد المنتجا من هذا البحث'=>$products->count(),
        'data'=>$products
    ] , 200);
  }

//    تابع لعرض تفاصيل منتج واحد مع الصور والمواصفات
//    هو عام و يمكن الكل استخدامه بدون الحاجة لصلاحيات خاصة
     public function show($productId)
{
   $product = Product::find($productId);
   if(!$product)
    {
     return response()->json(
        [
            'success'=>false,
            'message'=>'المنتج غير موجود او أنه محذوف مسبقا'
        ] , 403);
    }
    $product->load([
        'category'=>fn($q)=>$q->select('id' , 'name' , 'description'),
        'image'=>fn($q)=>$q->select('id' , 'product_id' , 'image_path'),
        'car_detail'=>fn($q)=>$q->select('id' , 'product_id' , 'brand' , 'model' , 'year' , 'engine_type' , 'plate_number' , 'color' , 'type'),
        'part_detail'=>fn($q)=>$q->select('id' , 'product_id' , 'name' , 'condition' , 'warranty' , 'manufacturer'),
    ]);
    //  الآن نقوم ببناء هيكل الرد الموحد
    $responseData = [
        'id'=>$product->id,
        'name'=>$product->name,
        'description'=>$product->description,
        'price'=>$product->price,
        'quantity'=>$product->quantity,
        'category'=>$product->category,
        'image'=>$product->images, // افترضت أن العلاقة في الموديل هي images وليس image
        'details'=>$product->type === 'car' ? $product->car_detail : $product->part_detail,
    ];

    // اذا كان المنتج سيارة نرجع تفاصيل السيارة و اذا كان قطعة نرجع تفاصيل القطعة
    if($product->type === 'car' && $product->car_detail)
        {
            //  تفاصيل السيارة
            $responseData['specifications'] = [
                'brand'=>$product->car_detail->brand,
                'model'=>$product->car_detail->model,
                'year'=>$product->car_detail->year,
                'engine_type'=>$product->car_detail->engine_type,
                'plate_number'=>$product->car_detail->plate_number,
                'color'=>$product->car_detail->color,
                'type'=>$product->car_detail->type, // body type 
            ];
        }
    elseif($product->type === 'part' && $product->part_detail)  
        {
          $responseData['specifications'] = [
            'name'=>$product->part_detail->name,
            'condition'=>$product->part_detail->condition,
            'warranty'=>$product->part_detail->warranty,
            'manufacturer'=>$product->part_detail->manufacturer,
          ];  
        }

            return response()->json([
        'success'=>true,
        'message'=>'تم جلب تفاصيل المنتج بنجاح',
        'data'=>$responseData
    ] , 200);

} 
//  تابع جلب منتجات تصنيف معين
public function getProductsByCategory($categoryId)
{
    $category = Category::find($categoryId);
    if(!$category)
    {
        return response()->json([
            'success' => false,
            'message' => 'التصنيف غير موجود او أنه محذوف مسبقا'
        ] , 403); 
    }
    $products = $category->products()->with('car_detail','part_detail')->paginate(10);
    return response()->json([
        'success' => true,
        'message' => 'تم جلب المنتجات التابعة لهذا التصنيف بنجاح',
        'عدد المنتجات التابعة لهذا الصنف هي :'=>$products->count(),
        'data' => $products
    ] , 200);
}
}
