<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
     $user = $request->user();
     $validated = $request->validate(
        [
         'order_id'=>'required|integer|exists:orders,id',
         'payment_method'=>'required|string',
         'transaction_id'=>'required|string' ,
         'status'=>'required|in:pending,success,failed,refunded',
        ]);
        //  التأكد من أن الطلب تابع للمستخدم و حالته غير معلقة و لم يدفع من قبل
        $order = Order::where('id' , $validated['order_id'])
        ->where('user_id' , $user->id)
        ->where('status' , 'pending')->first();

        if(!$order)
            {
           return response()->json([
            'success'=>false,
            'message'=>'الطلب غير متاح للدفع'
           ] , 404);
            }

        if($order->payment)
            {
            return response()->json(
                [
                    'success'=>false,
                    'message'=>'تم تهيئة دفعة لهذا الطلب مسبقاً'
                ] , 404);
            }

            // الآن نقوم بعملية الإنشاء
            $payment = Payment::create([
                'order_id'=>$order->id ,
                'payment_method'=>$validated['payment_method'],
                'transaction_id'=>$validated['transaction_id'],
                'status'=>$validated['status']
            ]);

            return response()->json([
                'success'=>true,
                'message'=>'تم تهيئة الدفعة بنجاح' ,
                'the payment is :'=>$payment
            ] , 201);
    }


    // عرض تفاصيل الدفع حسب رقم الدفع , و هذا من صلاحية صاحب الطلب فقط
    public function show(Request $request ,  $paymentId)
    {
    $user = $request->user();
     $payment = Payment::find($paymentId);
     if(!$payment)
        {
        return response()->json([
            'success'=>false,
            'message'=>'الدفع غير موجود لهذا الطلب'
        ] , 404);
        }

    if($payment->order->user_id !== $user->id && $user->role !=='admin')
        {
      return response()->json([
        'success'=>false ,
        'message'=>'ليس لديك صلاحية عرض هذا الدفع'
         ] , 403);
        }
      
    $payment->load('order:id,user_id,total_price,status');
    return response()->json([
        'success'=>true,
        'message'=>'تم جلب تفاصيل الدفع بنجاح',
        'data'=>$payment
    ] , 200);
    }


    


    // تابع يرجع حالة الدفع لطلب معين بناءا على رقمه
    public function getPaymentStatus(Request $request , $orderId)
    {
    $user = $request->user();
    $orderId = Order::find($orderId);
    if(!$orderId)
        {
        return response()->json([
            'success'=>false,
            'message'=>'الطلب الذي تريد عرض بيانات الدفع الخاصة فيه غير موجود'
        ] , 404);
        }

    if($orderId->user_id !== $user->id)
        {
      return response()->json([
        'success'=>false ,
        'message'=>'ليس لديك صلاحية عرض بيانات الدفع لهذا الطلب'
         ] , 403);
        }
    
    $payment = Payment::where('order_id' , $orderId->id)->first();
    if(!$payment)        {
        return response()->json([
            'success'=>false,
            'message'=>'لا يوجد دفعة لهذا الطلب'
        ] , 404);
    }
    return response()->json([
        'success'=>true,
        'message'=>'تم جلب حالة الدفع بنجاح',
        'data'=>[
            'payment_status'=>$payment->status,
            'payment_method'=>$payment->payment_method,
            'transaction_id'=>$payment->transaction_id,
            'order_total_price'=>$payment->order->total_price,
            'order_status'=>$payment->order->status
        ]
     ] , 200);

    }





    // إلغاء الدفع لطلب معين و هذا من صلاحية صاحب الطلب فقط
    public function cancelPayment(Request $request , $paymentId)
    {
        $user = $request->user();
        $payment = Payment::find($paymentId);
        if(!$payment)            {
            return response()->json([
                'success'=>false,
                'message'=>'الدفع الذي تريد إلغاؤه غير موجود'
            ] , 404);
        }
    if($payment->order->user_id !== $user->id)
        {
        return response()->json([
            'success'=>false,
            'message'=>'غير مصرح لك بإلغاء حالة دفع لا تنتمي لك'
            ] , 403);
        }

    //  لا يمكن إلغاء دفعة ملغية مسبقا
    if($payment->status == 'failed')
        {
       return response()->json([
        'success'=>false,
        'message'=>'لا يمكنك إلغاء حالة دفع ملغية مسبقا'
       ] , 403);
        }

    $payment->update(['status'=>'failed']);
    $payment->order->update(['status'=>'pending']); // أي إعادة حالة الدفع إلى معلقة
    return response()->json([
        'success'=>true,
        'message'=>'تم إلغاء حالة الدفع و تحديث حالة الطلب  '
    ] , 200);
    }
    

//  دالة عرض جميع حالات الدفع لدي 
    public function showAllPayments()
    {
    $user = Auth::user();
    $payments = Payment::whereHas('order' , function($query) use ($user)
    {
        $query->where('user_id' , $user->id);
    })->get();
    return response()->json([
        'success'=>true,
        'message'=>'تم جلب جميع حالات الدفع الخاص بك بنجاح',
        'data'=>$payments
    ] , 200);
}

}