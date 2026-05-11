<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
     $validated = $request->validate(
        [
            'first_name'=>'required|string|max:100',
            'last_name'=>'required|string|max:100',
            'username'=>'required|string|max:100',
            'address'=>'nullable|string|max:100',
            'phone_number'=>'nullable|string|max:15',
            'role'=>'sometimes|in:admin,user',
            'email'=>'required|email|max:255|unique:users',
            'password'=>'required|string|confirmed|min:6|max:100',
        ]);

        // إنشاء المستخدم 
        $user = User::create([
           'username'     => $validated['username'],
            'first_name'   => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'email'        => $validated['email'],
            'password'=>Hash::make($validated['password']),
            'phone_number'=>$validated['phone_number'] ,
            'address'=>$validated['address'],
            'role'=>$validated['role']
            ]);

            return response()->json(
                [
                    'success'=> true ,
                    'message'=>'تم إنشاء الحساب بنجاح' ,
                    $user
                   
                    ] , 201);
    } 
  
    
    //  تسجيل الدخول
    public function login(Request $request)
    {
     $validated = $request->validate(
        [
            'email'=>'required|email|max:255',
            'password'=>'required|string|min:6|max:100',
        ]);

        $user = User::where('email' , $validated['email'])->first();

        if(!$user || !Hash::check($validated['password'] , $user->password)){
            return response()->json([
                'success'=> false ,
                'message'=>'بيانات الدخول غير صحيحة , لديك خطأ في البريد الإلكتروني أو كلمة المرور'
            ] , 401);
        }

        // إنشاء توكن للمستخدم
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success'=> true ,
            'message'=>'تم تسجيل الدخول بنجاح' ,
            'date of user'=>$user ,
            'access_token'=>$token
        ] , 200);
    }

    // تسجيل الخروج
    public function logout(Request $request)
    {        $request->user()->currentAccessToken()->delete();  
        return response()->json([
            'success'=> true ,
            'message'=>'تم تسجيل الخروج بنجاح' 
        ] , 200);
    }
}
