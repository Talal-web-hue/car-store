<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


//  Authntication Api
Route::post('register' , [AuthController::class , 'register']);
Route::post('login' , [AuthController::class , 'login']);
Route::post('logout' , [AuthController::class , 'logout'])->middleware('auth:sanctum');


// User Api
Route::get('users' , [UserController::class , 'index'])->middleware('auth:sanctum');
Route::get('users/{id}' , [UserController::class , 'profile'])->middleware('auth:sanctum');
Route::put('users/{id}' , [UserController::class , 'update'])->middleware('auth:sanctum');
Route::put('users/{id}/password' , [UserController::class , 'updatePassword'])->middleware('auth:sanctum');
Route::delete('users/{id}' , [UserController::class , 'destroy'])->middleware('auth:sanctum');

// category Api 
Route::post('categories' , [CategoryController::class , 'store'])->middleware('auth:sanctum');  // هذه خاصة للآدمن فقط هو الذي يقوم بإنشاء التصنيفات
Route::put('categories/update/{categoryId}' , [CategoryController::class , 'update'])->middleware('auth:sanctum');  // هذه خاصة للآدمن فقط هو الذي يقوم بإنشاء التصنيفات
Route::delete('categories/destroy/{categoryId}' , [CategoryController::class , 'destroy'])->middleware('auth:sanctum');  // هذه خاصة للآدمن فقط هو الذي يقوم بحذف التصنيفات
Route::get('getCategory/{id}' , [CategoryController::class , 'getDetailsCategory']); // للعملاء و التصفح
Route::get('getCategoriesWithProducts/{id}' , [CategoryController::class , 'getCategoryWithProducts']); // للعملاء و التصفح
Route::get('searchCategory' , [CategoryController::class , 'searchCategories']);

// product Api
Route::post('store' , [ProductController::class , 'store'])->middleware('auth:sanctum'); // هذه خاصة للآدمن فقط هو الذي يقوم بإنشاء المنتجات    
Route::put('update/{productId}' , [ProductController::class , 'update'])->middleware('auth:sanctum'); // هذه خاصة للآدمن فقط هو الذي يقوم بتحديث المنتجات    
Route::delete('destroy/{productId}' , [ProductController::class , 'destroy'])->middleware('auth:sanctum'); // هذه خاصة للآدمن فقط هو الذي يقوم بتحديث المنتجات    
Route::get('index' , [ProductController::class , 'index']); // للعملاء و التصفح
Route::get('search' , [ProductController::class , 'search']); // للعملاء و التصفح
Route::get('show/{id}' , [ProductController::class , 'show']); // للعملاء و التصفح
Route::get('getProductDetails/{id}' , [ProductController::class , 'getProductsByCategory']); // للعملاء و التصفح
Route::get('getStockStatus/{id}' , [ProductController::class , 'getStockStatus']); // للعملاء و التصفح




// Image Api
Route::post('products/{product}/images' , [ImageController::class , 'store'])->middleware('auth:sanctum'); // هذه خاصة للآدمن فقط هو الذي يقوم بإنشاء المنتجات
Route::put('productsUpdate/{product}/images' , [ImageController::class , 'update'])->middleware('auth:sanctum'); // هذه خاصة للآدمن فقط هو الذي يقوم بتحديث الصور
Route::delete('products/{product}/images/{image}' , [ImageController::class , 'delete'])->middleware('auth:sanctum'); // هذه خاصة للآدمن فقط هو الذي يقوم بحذف الصور
Route::get('getImagesProduct/{productId}' , [ImageController::class , 'getImages']); // عام أي يحق لجميع الزوار مشاهدة صور منتج ما



// Favorite Api
Route::post('favorites' , [FavoriteController::class , 'addToFavorite'])->middleware('auth:sanctum');
Route::get('getFavorites' , [FavoriteController::class , 'getFavorites'])->middleware('auth:sanctum');
Route::delete('favorites/{id}' , [FavoriteController::class , 'removeFromFavorite'])->middleware('auth:sanctum');
Route::delete('clearFavorites' ,[FavoriteController::class , 'clearFavorites'])->middleware('auth:sanctum'); // تابع لحذف كل المنتجات من المفضلة مرة واحدة


// Review Api
Route::post('storeReview' , [ReviewController::class , 'store'])->middleware('auth:sanctum');
Route::get('index/{productId}' , [ReviewController::class , 'index']); // عام أي يحق لجميع الزوار مشاهدة تقييمات منتج ما
Route::put('updateReview/{reviewId}' , [ReviewController::class , 'update'])->middleware('auth:sanctum'); // هذه خاصة للمستخدم المسجل الدخول 
Route::delete('destroyReview/{reviewId}' , [ReviewController::class , 'destroy'])->middleware('auth:sanctum'); // هذه خاصة للمستخدم المسجل الدخول, و الأدمن أيضاً يستطيع حذف أي تقييم لأنه يملك صلاحيات أعلى من المستخدم العادي
Route::get('checkProduct/{productId}' , [ReviewController::class , 'check'])->middleware('auth:sanctum');
Route::get('myReviews' , [ReviewController::class , 'myReviews'])->middleware('auth:sanctum');


// Order Api 
Route::post('storeOrder' , [OrderController::class , 'store'])->middleware('auth:sanctum'); // هذه خاصة للمستخدم المسجل الدخول هو الذي يقوم بإنشاء الطلبات
Route::get('showOrders' , [OrderController::class , 'showOrders'])->middleware('auth:sanctum'); // هذه خاصة للمستخدم المسجل الدخول هو الذي يقوم بعرض طلباته
Route::get('getOrderDetails/{order}' , [OrderController::class , 'showDetails'])->middleware('auth:sanctum'); // هذه خاصة للمستخدم المسجل الدخول هو الذي يقوم بعرض تفاصيل طلب واحد
Route::put('updateOrder/{orderId}' , [OrderController::class , 'update'])->middleware('auth:sanctum'); // هذه خاصة للآدمن فقط هو الذي يقوم بتحديث حالة الطلب
Route::delete('deleteOrder/{orderId}' , [OrderController::class , 'delete'])->middleware('auth:sanctum'); // هذه خاصة للمستخدم المسجل
Route::put('cancelOrder/{order}' , [OrderController::class , 'cancel'])->middleware('auth:sanctum'); // هذه خاصة للمستخدم المسجل


//  Payment Api
Route::post('store' , [PaymentController::class , 'store'])->middleware('auth:sanctum');  // هي صلاحية لمالك الطلب فقط
Route::get('show/payment/{paymentId}' , [PaymentController::class , 'show'])->middleware('auth:sanctum');  // هي صلاحية لمالك الطلب فقط
Route::get('getPaymentStatus/{orderId}' , [PaymentController::class , 'getPaymentStatus'])->middleware('auth:sanctum');  // هي صلاحية لمالك الطلب فقط
Route::patch('cancelPayment/{paymentId}' , [PaymentController::class , 'cancelPayment'])->middleware('auth:sanctum');  // هي صلاحية لمالك الطلب فقط
Route::get('showAllPayments' , [PaymentController::class , 'showAllPayments'])->middleware('auth:sanctum');  // هي صلاحية لمالك الطلب فقط



// Shipment Api
Route::post('store/shipment' , [ShipmentController::class, 'store'])->middleware('auth:sanctum');
Route::get('showShipment/{orderId}' , [ShipmentController::class, 'showShipment'])->middleware('auth:sanctum');
Route::post('cancelShipment/{orderId}' , [ShipmentController::class, 'cancelShipment'])->middleware('auth:sanctum'); // أدمن فقط
Route::get('showAllShipments' , [ShipmentController::class, 'showAllShipments'])->middleware('auth:sanctum'); // أدمن فقط
