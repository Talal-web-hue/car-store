<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProductController;
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