<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
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