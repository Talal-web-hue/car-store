<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    //  دالة إنشاء تصنيف جديد
    // طبعا هي من صلاحية المدير , هو الذي يقوم بإنشاء تصنيفات
    public function store(Request $request)
    {
     $user = $request->user();
     if($user->role !== 'admin')
     {
        return response()->json([  
            'success' => false,
            'message' => 'غير مصرح لك بالوصول إلى هذه البيانات'
        ], 403);
    }
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:categories,name',
        'description'=>'required|string|max:1000',
    ]); 
 
    $category = Category::create($validated);
    return response()->json([
        'success' => true,
        'message' => 'تم إنشاء التصنيف بنجاح',
        'data' => $category
    ]);
}

//   تحديث تصنيف من قبل الأدمن أيضا
  public function update(Request $request , $categoryId)
  {
    $user = $request->user();
   if($user->role !== 'admin')
    {
     return response()->json([
        'success'=>false ,
        'message'=>'غير مصرح لك بالوصول إلى هذه البيانات'
     ],403);
    }
    $category = Category::find($categoryId);
    if(!$category){
       return response()->json(
        [
            'success'=>false,
            'message'=>'التنيف غير موجود او أنه محذوف مسبقا'
        ]);
    }
    $validated = $request->validate([
        'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $categoryId,
        'description'=>'sometimes|required|string|max:1000',
    ]);
    $category->update($validated);
    return response()->json([
        'success'=>true ,
        'message'=>'تم تحديث التصنيف بنجاح',
    ] , 200);
    }

    //  حذف التصنيف من قبل الأدمن أيضا
    public function destroy(Request $request , $categoryId)
    {
        $user = $request->user();
        if($user->role !== 'admin')
        {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول إلى هذه البيانات'
            ], 403);
        }
     $category = Category::find($categoryId);
     if(!$category){
        return response()->json([
            'success' => false,
            'message' => 'التنيف غير موجود او أنه محذوف مسبقا'
        ] , 403); 
    }
    $category->delete();
    return response()->json([
        'success' => true,
        'message' => 'تم حذف التصنيف بنجاح',
    ] , 200);
}

//  جلب تفاصيل تصنيف 
 public function getDetailsCategory($categoryId)
 {
   $category = Category::find($categoryId);
   if(!$category)
    {
      return response()->json(
        [
            'success' => false,
            'message' => 'التصنيف غير موجود او أنه محذوف مسبقا'
        ] , 403);   
       
    }
    return response()->json([
        'success' => true,
        'message' => 'تم جلب تفاصيل التصنيف بنجاح',
        'data' => $category
    ] , 200);
 }


//   تابع جلب التصنيف مع المنتجات التي تتبع له 
 public function getCategoryWithProducts($categoryId)
 {
    $category = Category::with('products')->find($categoryId);
    if(!$category)
    {
        return response()->json([
            'success' => false,
            'message' => 'التصنيف غير موجود او أنه محذوف مسبقا'
        ] , 403); 
    }
    return response()->json([
        'success' => true,
        'message' => 'تم جلب التصنيف مع المنتجات التابعة له بنجاح',
        'data' => $category
    ] , 200);
 }

//   تابع البحث عن تصنيف ما حسب الاسم أو التصنيف

public function searchCategories(Request $request)
{
    $query = trim($request->input('query'));

    if (empty($query)) {
        return response()->json([
            'success' => false,
            'message' => 'يرجى إدخال مصطلح للبحث',
            'data'    => []
        ], 422);
    }

    $categories = Category::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%");
        })->paginate(5);

    return response()->json([
        'success' => true,
        'message' => 'تم البحث عن التصنيفات بنجاح',
        'data'    => $categories
    ], 200);
}
}