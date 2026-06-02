<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // تابع الإضافة للمفضلة مع منع تكرار المنتج في المفضلة
    //  المستخدمون المسجلون فقط هم الذين يمكنهم إضافة منتجات إلى المفضلة
    public function addToFavorite(Request $request)
    {
     $user = $request->user(); // الحصول على المستخدم الحالي من الطلب
     $validated = $request->validate([
        'product_id'=>'required|integer|exists:products,id',
     ]);
    //  منع التكرار , الإنشاء الذكي أي اذا لم يوجد منتج نضيفه للمفضلة يقوم إنشاء سجل جديد
    $favorite = Favorite::firstOrCreate(
        [
            'user_id'=>$user->id,
            'product_id'=>$validated['product_id']
        ]);
        // التحقق مما إذا تم إنشاء سجل جديد أو إذا كان موجودًا بالفعل
        if($favorite->wasRecentlyCreated){
            return response()->json(['message'=>'Product added to favorites successfully.',
            'data'=>$favorite->load('product:id,name,price,quantity')
            ] , 201);
        }else{
            {
                return response()->json(['message'=>'Product is already in favorites.']);
            } 
    }

}

//    تابع لعرض المنتجات التي في المفضلة
    //   تكون من صلاحية المستخدم الذي يسجل دخول فقط
    public function getFavorites(Request $request)
    {
        $user = $request->user();
        $favorites = $user->favorites()->with([
            'product:id,name,price,quantity'
        ])->paginate(10);   // لعرض كل عشر منتجات في صحفة واحدة
      
        return response()->json(
            [
                'success'=>true,
                'message'=>'إليك قائمة مفضلتك',
                'data'=>$favorites] , 200);
    }


    // تابع لحذف منتج من المفضلة
    public function removeFromFavorite(Request $request , $id)
    {
     $user = $request->user();
     $product = Favorite::where('user_id' , $user->id)->where('product_id' , $id)->first();
     if(!$product){
        return response()->json(['message'=>'Product not found in favorites.'] , 404);
     }else{
        $product->delete();
        return response()->json(['message'=>'Product removed from favorites successfully.'] , 200);     

    }
}


//    تابع تفريغ المفضلة بالكامل
   public function clearFavorites(Request $request)
   {
    $user = $request->user();
    $user->favorites()->delete();
    return response()->json(['message'=>'Favorites cleared successfully.'], 200);
   }
}

