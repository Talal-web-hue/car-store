<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    // تابع رفع صور جديدة , و هي من صلاحيات الأدمن 
public function store(Request $request, Product $product)
{ 
    $user = $request->user();
    
    //  التحقق من الصلاحية
    if ($user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    //  تصحيح أسماء الحقول في الـ Validation (images بدلاً من image)
    $validated = $request->validate([
        'images' => 'required|array|min:1|max:10',
        'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
    ]);

    $uploadedImages = [];
    
    //  تصحيح طريقة الوصول للملفات: استخدام $request->file('images')
    if ($request->hasFile('images')) {
        foreach($request->file('images') as $index => $file){
            
            // تخزين الصورة في مجلد خاص بالمنتج
            $path = $file->store('products/' . $product->id, 'public');
            $url = Storage::url($path);
            
            $image = $product->images()->create([
                'image_url' => $url,
                // يمكنك إضافة: 'alt_text' => $request->input("alt_text.{$index}")
            ]);
            $uploadedImages[] = $image;
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Images uploaded successfully',
        'data' => $uploadedImages
    ], 201);
}




//   تابع حذف صورة
//   من صلاحيات الأدمن
  public function delete(Request $request , Product $product , Image $image)
  {
    $user = $request->user();
    if($user->role !=='admin')
        {
         return response()->json(['message' => 'غير مصرح لك بحذف صور للمنتج , هذه من صلاحيات الأدمن'], 403);
        } 
        //  فحص الصورة هل هي موجودة ام لا 
        $image = Image::find($image);
        if(!$image)
            {
              return response()->json(
                [
                    'success'=>true,
                    'message'=>'الصورة غير موجودة أو أنك قمت بحذفها'
                ] , 403);
            }
    // التأكد من أن الصور التي نريد حذفها هي تتبع لهذا المنتج أم لأ
    if($image->product_id !== $product->id)
        {
         return response()->json([
            'success'=>false,
            'message'=>'الصورة التي تريد حذفها لا تتبع لهذا المنتج'
         ] , 403);
        }
    $relativePath = preg_replace('#^.*?/storage/#', '', $image->image_url);
    if(Storage::disk('public')->exists($relativePath))
        {
         Storage::disk('public')->delete($relativePath);
        }  

        //  حذف الصور من قاعدة البيانات
        $image->delete();
        return response()->json([
            'success'=>true,
            'message'=>'تم حذف الصورة بنجاح'
        ] , 200);
  }





//    تابع لعرض تفاصيل صور منتج معين
//    تكون من صلاحية الكل
    public function getImages($idProduct)
    {
     $product = Product::find($idProduct);    
     if(!$product)
        {
         return response()->json(
            [
                'success'=>false ,
                'message'=>'the product is not found or you deleted his'
            ] , 403);
        }

        $data = $product->images()->get();
        return response()->json([
            'success'=>true ,
            'message'=>'صور هذا المنتج هي' ,
            'عدد صور هذا المنتج هي :'=>$data->count(),
            'data'=>$data,
            'product_id'=>$product->id,  // رقم المنتج الذي تتبع له هذه الصور
            'product_name'=>$product->name,
            'product_price'=>$product->price,
            'product_type'=>$product->type            
        ] , 200);
    }


    //  تابع التعديل على الصور
    //  هي من صلاحية الأدمن فقط

    public function update(Request $request, Product $product, Image $image)
{
    //  صلاحية الأدمن
    if ($request->user()?->role !== 'admin') {
        return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
    }
    //  حماية الملكية
    if ($image->product_id !== $product->id) {
        return response()->json(['success' => false, 'message' => 'الصورة غير تابعة لهذا المنتج'], 404);
    }

    //  التحقق (كلا الحقلين اختياري)
    $validated = $request->validate([
        'alt_text' => 'nullable|string|max:255',
        'image'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    //  استبدال الملف إذا وُجد
    if ($request->hasFile('image')) {
        $oldPath = preg_replace('#^.*?/storage/#', '', $image->image_url);
        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
        $newPath = $request->file('image')->store('products/' . $product->id, 'public');
        $validated['image_url'] = Storage::url($newPath);
    }

    $image->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'تم تحديث الصورة بنجاح',
        'data'    => $image->fresh()
    ]);
}
}