<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShipmentController extends Controller
{
//     إنشاء شحنة لطلب و هي من صلاحيات الأدمن
   public function store(Request $request)
   {
    $user = $request->user();
    if($user->role !== 'admin')
        {
       return response()->json(
        [
            'success'=>false ,
            'message'=>'غير مصرح لك بإنشاء طلب توصيل , هذه من صلاحيات الأدمن'
        ] , 403);
        }    

        // التحقق من المدخلات
        $validated = $request->validate([
            'order_id'=>'required|integer|exists:orders,id',
            'address'=>'required|string|max:255',
            'city'=>'required|string|max:100',
            'status'=>'nullable|in:pending,shipped,delivered'
        ]);

        $order = Order::find($validated['order_id']);
        // منع الشحن لطلبات غير مؤهلة
        if($order->status === 'shipping' || $order->status === 'delivered')
        {
        return response()->json([
            'success'=>false,
            'message'=>'لا يمكن تعيين شحنة لطلب في حالة تم توصيله أو شحنه بالفعل'
        ] , 403);
        } 

        // منع تكرار الشحنة لنفس الطلب
        if($order->shipment)
            {
            return response()->json([
                'success'=>false,
                'message'=>'يوجد سجل شحن مسجل مسبقا لهذا الطلب'
            ] , 409); 
            }
        // إنشاء الشحنة
        $shipment = Shipment::create([
            'order_id'=>$order->id,
            'address'=>$validated['address'],
            'city'=>$validated['city'],
            'status'=>$validated['status']
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'تم إنشاء الشحنة بنجاح',
            'data'=>$shipment
        ] , 201);
   }



   
   // عرض تفاصيل شحنة معينة
   public function showShipment(Request $request , Order $orderId)
   {
    $user = $request->user();
    if($user->role !== 'admin' && $orderId->user_id !== $user->id)
        {
         return response()->json([
            'success'=>false,
            'message'=>'غير مصرح لك بعرض التفاصيل'
         ] , 403);
        }

    $orderId->load('shipment'); 
    if(!$orderId->shipment)
        {
        return response()->json([
            'success'=>false,
            'message'=>'لم يتم تعيين شحنة لهذا الطلب'
        ] , 404);
        }
    $shipment = $orderId->shipment;
    return response()->json([
        'success'=>true,
        'message'=>'تم جلب بيانات الشحنة بنجاح',
        'data'    => [
            'shipment_id' => $shipment->id,
            'order_id'    => $shipment->order_id,
            'address'     => $shipment->address,
            'city'        => $shipment->city,
            'status'      => $shipment->status,
            'created_at'  => $shipment->created_at->format('Y-m-d H:i:s'),
            'updated_at'  => $shipment->updated_at->format('Y-m-d H:i:s')
        ]
    ] , 200);
   }





//    تابع إلغاء شحنة
 
  public function cancelShipment(Request $request , Order $orderId)
  {
   $user = $request->user();
   if($user->role !=='admin')
    {
   return response()->json([
    'success'=>false,
    'message'=>'غير مصرح لك بإلغاء الشحنة'
   ] , 403);
    }

  $shipment = $orderId->shipment;
  if(!$shipment)
    {
     return response()->json([
        'success'=>false,
        'message'=>'لا توجد  شحنة لهذا الطلب لكي تقوم بإلغائها'
     ] , 404);
    }

    // إذا كانت الشحنة مسلمة لا يمكننا إلغائها
    if($shipment->status === 'delivered')
        {
       return response()->json([
        'success'=>false,
        'message'=>'لا يمكنك إلغاء شحنة تم تسليمها'
       ] , 422);
        }

    $shipment->update(['status'=>'pending']);
    return response()->json([
        'success'=>true,
        'message'=>'تم تحديث الحالة إلى معلقة بنجاح'
    ] , 200);


  }





//   تابع عرض جميع الشحنات للأدمن فقط
 
public function showAllShipments()
{
 $user = Auth::user();  // لجلب المستخدم الحالي
 if($user->role !=='admin')
    {
     return response()->json([
        'success'=>false,
        'message'=>'غير مصرح لك بعرض جميع الشحنات , هذه من خصائص الادمن'
     ] , 403);
    }

 $shipments = Shipment::all();
 return response()->json([
    'success'=>true,
    'message'=>'إليك جميع الشحنات المتوفرة لديك',
    'data'=>$shipments
 ] , 200);
}



//  تابع يقوم بجلب الشحنات للأدمن حسب حقل البحث الذي يقوم بإدخاله

public function index(Request $request)
{
    $user = $request->user();
    if ($user->role !== 'admin') {
        return response()->json(['success' => false, 'message' => 'غير مصرح لك '], 403);
    }

}
}
