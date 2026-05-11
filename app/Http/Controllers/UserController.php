<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //  تابع جلب جميع المستخدمين
    public function index()
    {
        $user = Auth::user();
        if($user->role !== 'admin')
            { 
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالوصول إلى هذه البيانات'
                ], 403);
            }
        $users = User::all();
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    //  عرض الملف الشخصي للمستخدم
    public function profile(Request $request)
    {
        $user = $request->user()->makeHidden(['password', 'remember_token']);
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
}

//    تحديث بيانات المستخدم
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'address' => 'sometimes|string|max:100',
            'phone_number' => 'sometimes|string|max:15',
            'username' => 'sometimes|string|max:100|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'data' => $user
        ]);
    }

    //  تحديث كلمة المرور
    public function updatePassword(Request $request)
    {
        $user = $request->user();   
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);
        if((!$user || !Hash::check($validated['current_password'], $user->password))){
            return response()->json([
                'success' => false,
                'message' => 'كلمة المرور الحالية غير صحيحة'
            ], 401);
        }
        $user->password = Hash::make($validated['new_password']);
        $user->save();  
        return response()->json([
            'success' => true,
            'message' => 'تم تحديث كلمة المرور بنجاح'
        ]);
}

}