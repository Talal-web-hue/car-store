<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // تابع إنشاء تقييم
    public function store(Request $request)
    {
      $user = $request->user();
      $validated = $request->validate(
        [
            'product_id'=>'required|integer|exists:products,id',
            'rating'=>'required|integer|min:1|max:5',
            'comment'=>'nullable|string|max:1000'
        ]);
        // منع تكرار نفس التقييم لنفس المنتج
        if(Review::where('user_id' , $user->id)->where('product_id' , $validated['product_id'])->exists())
            {
             return response()->json(
                [
                    'success'=>false,
                    'message'=>'لقد قمت بتقييم هذا المنتج مسبقاً'
                ] , 403);
            }

            $review = $user->reviews()->create($validated);
            return response()->json(
                [
                    'success'=>true,
                    'message'=>'تم إنشاء التقييم بنجاح',
                    'data'=>$review
                ] , 201);
    }

    // عرض تقييمات منتج معين
    public function index($productId)
    {
     $product = Product::find($productId);
     if(!$product)
        {
          return response()->json([
            'success'=>false,
            'message'=>'المنتج غير موجود لكي نقوم بعرض تقييماته لك'
          ] , 403);
        }
        $review = $product->reviews()->with('user:id,first_name,last_name,username')->get();
        return response()->json([
            'success'=>true ,
            'عدد تقييمات هذا المنتج هيك'=>$review->count(), 
            'message'=>'إليك تقييمات هذا المنتجات',
            'data'=>$review
        ] , 200);
    }

    // تعديل تقييم منتج
    public function update(Request $request , $reviewId)
    {
     $user = $request->user();
     $review = Review::find($reviewId);
     if(!$review)
        {
          return response()->json([
            'success'=>false,
            'message'=>'التقييم الذي تريد التعديل عليه غير موجود'
          ] , 403);
        }
     $validated = $request->validate([
        'rating'=>'sometimes|integer|min:1|max:5',
        'comment'=>'nullable|string|max:1000'
     ]);

     $review->update($validated);
     return response()->json([
        'success'=>true ,
        'message'=>'تم تحديث التقييم بنجاح'
     ] , 200);

    }

    // حذف تقييم منتج
    public function destroy(Request $request ,  $reviewId)
    {
        $user = $request->user();
        $review = Review::find($reviewId);
        if(!$review)
            {
             return response()->json([
                'success'=>false,
                'message'=>'The Review is not found'
             ] , 403);
            }
        if($review->user_id !== $user->id && $user->role !== 'admin')
            {
   
           return response()->json([
            'success'=>false,
            'message'=>'ليس لديك صلاحية حذف هذا التقييم'
           ] , 403);
            }
           $review->delete();
           return response()->json([
            'success'=>true,
            'message'=>'تم حذف التقييم بنجاح'
           ] , 200);          
    }

    

    // تابع يقوم بفحص هل المستخدم قيّم هذا المنتج أم لا
   
public function check(Request $request, $productId)
{
    $hasReviewed = $request->user()->reviews()
        ->where('product_id', $productId)
        ->exists();

    return response()->json([
        'success'      => true,
        'has_reviewed' => $hasReviewed,
        'message'      => $hasReviewed ? 'لقد قيّمت هذا المنتج مسبقاً' : 'لم تقم بتقييم هذا المنتج بعد'
    ]);
}




    // تابع عرض جميع تقييمات المستخدم الحالي
    // الهدف من هذا التابع : صفحة تقييماتي , حيث يرى المستخدم كل المنتجات التي قيّمها

    public function myReviews(Request $request)
    { 
     $user= $request->user();
     $review = $user->reviews()->with('product:id,name,price,quantity')->
     paginate(10);
    //  تنسيق الرد للواجهة الأمامية
    $formattedData = $review->map(fn($r)=>
    [
        'review_id'=>$r->id,
        'product_id'=>$r->product_id,
        'product_name'=>$r->product?->name,
        'rating'=>$r->rating,
        'comment'=>$r->comment,
        'created_at'=>$r->created_at?->format('Y-m-d H:i:s')
    ]);
    return response()->json(
        [
            'success'=>true,
            'message'=>'تم جلب تقييماتك بنجاح' ,
            'total'=>$review->total(),
            'review'=>$formattedData
        ] , 200);
    }


}
