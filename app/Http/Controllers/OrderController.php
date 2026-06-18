<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // إنشاء طلب 
//     public function store(Request $request)
//     {
//     //   تابع إنشاء طلب  , و هي من صلاحية المتسخدم الذي سجل الدخول 
//        $user = $request->user();
//        $validated = $request->validate([
//         'items'=>'required|array|min',
//         'items.*.product_id'=>'required|integer|exists:products,id',
//         'items.*.quantity'   => 'required|integer|min:1|max:100',
//         'order_date'=>'nullable|date'
//        ]);
//     //    فحص المخزون و حساب المجموع قبل إنشاء الطلب
//     $totalPrice = 0;
//     $orderItmeData = [];
//     foreach($validated['item'] as $item)
//         {
//         $product = Product::find($item['product_id']); // هنا نتحقق هل المنتج موجود ضمن جدول order_item
//         // نتحقق من توفر الكمية
//         if($product->quantity < $item['quantity'])
//             {
//             return response()->json(
//                 [
//                     'success'=>false,
//                     'message'=>'الكمية المطلوبة من "{$product->name} . غير متوفرة , المتبقي . {$product->quantity}"'
//                 ] , 422);
//             }
//         //   حساب السعر 
//         $unitPrice = $product->price;
//         $subtotal = $unitPrice * $item['quantity'];
//         $totalPrice+= $subtotal;

//         $orderItemData[] = [
//          'product_id'=>$product->id,
//          'quantity'=>$item['quantity'],
//         ];

//         }
//         // إنشاء سجل الطلب الرئيسي
//         $order = DB::transaction(function () use ($user, $validated, $totalPrice, $orderItemsData) {
//         $order = $user->orders()->create([
//          'order_date'  => $validated['order_date'] ?? now(),
//          'status'=>'pending',
//          'totalPrice'=>$totalPrice
//         ]);
//         // }

//         // إنشاء عناصر الطلب المرتبطة
//              foreach ($orderItemsData as $itemData) {
//             $order->items()->create($itemData);
//         }
//                // ج) خصم الكميات من مخزون المنتجات (بعد نجاح إنشاء الطلب)
//         foreach ($orderItemsData as $itemData) {
//             Product::where('id', $itemData['product_id'])
//                 ->decrement('quantity', $itemData['quantity']);
//         }
//         return $order; 
//     });   
// }

//   

public function store(Request $request)
{
    $user = $request->user();

    $validated = $request->validate([
        'items'      => 'required|array|min:1',
        'items.*.product_id' => 'required|integer|exists:products,id',
        'items.*.quantity'   => 'required|integer|min:1|max:100',
        'order_date' => 'nullable|date|after_or_equal:now',
    ]);

    $totalPrice = 0;
    $orderItemsData = [];

    foreach ($validated['items'] as $item) {
        $product = Product::find($item['product_id']);

        if ($product->quantity < $item['quantity']) {
            return response()->json([
                'success' => false,
                'message' => "الكمية المطلوبة من '{$product->name}' غير متوفرة. المتبقي: {$product->quantity}"
            ], 422);
        }

        $unitPrice = $product->price;
        $subtotal  = $unitPrice * $item['quantity'];
        $totalPrice += $subtotal;

        $orderItemsData[] = [
            'product_id' => $product->id,
            'quantity'   => $item['quantity'],
            'unit_price' => $unitPrice, 
        ];
    }

    $order = DB::transaction(function () use ($user, $validated, $totalPrice, $orderItemsData) {
        
        $order = $user->orders()->create([
            'order_date'  => $validated['order_date'] ?? now(),
            'status'      => 'pending', // الحالة الافتراضية
            'total_price' => $totalPrice,
        ]);

        foreach ($orderItemsData as $itemData) {
            $order->order_item()->create($itemData);
        }

        foreach ($orderItemsData as $itemData) {
            Product::where('id', $itemData['product_id'])
                ->decrement('quantity', $itemData['quantity']);
        }

        return $order;
    });

$order->load(['order_item.product:id,name']);

$itemsCollection = $order->items ?? collect([]);

return response()->json([
    'success' => true,
    'message' => 'تم إنشاء طلبك بنجاح',
    'data'    => [
        'order_id'    => $order->id,
        'order_date'  => $order->order_date,
        'status'      => $order->status,
        'total_price' => number_format($order->total_price, 2),
        'items_count' => $itemsCollection->count(),
        'items'       => $itemsCollection->map(fn($item) => [
            'product_name' => $item->product?->name ?? 'منتج غير موجود',
            'quantity'     => $item->quantity,
            'unit_price'   => number_format($item->unit_price, 2),
            'subtotal'     => number_format($item->quantity * $item->unit_price, 2),
        ])
    ]
], 201);
}

 
  // تابع تحديث حالة الطلب للأدمن فقط
//   التعديل على حالة الطلب يعني 
// اي العميل يقوم بإلغاء طلبه
  public function update()
  {
   
  } 



//    تابع عرض تفاصيل طلب واحد
public function showDetails(Request $request, Order $order)
{
    if (!$order->exists) {
        return response()->json([
            'success' => false,
            'message' => 'الطلب غير موجود'
        ], 404);
    }

    $user = $request->user();
    if ($user->role !== 'admin' && $order->user_id !== $user->id) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك بعرض تفاصيل هذا الطلب'
        ], 403);
    }

$order->load(['order_item.product:id,name']);

$itemsCollection = $order->order_item ?? collect([]);

return response()->json([
    'success' => true,
    'message' => 'تم جلب تفاصيل الطلب بنجاح',
    'data'    => [
        'order_id'    => $order->id,
        'order_date'  => $order->order_date,
        'status'      => $order->status,
        'total_price' => number_format($order->total_price, 2),
        'items'       => $itemsCollection->map(fn($item) => [
            'product_name' => $item->product?->name ?? 'منتج محذوف',
            'quantity'     => $item->quantity,
            'unit_price'   => number_format($item->unit_price, 2),
            'subtotal'     => number_format($item->quantity * $item->unit_price, 2),
        ])
    ]
]);
}



//    تابع عرض طلبات المستخدم الحالي
   public function showOrders()
   {    // الخطأ التالي هو خطأ من البيئة 
    $orders = auth()->user()->orders()->with('order_item.product:id,name')->get(); 
    return response()->json([
        'success' => true,
        'data' => $orders
    ] , 200);
   }


   // تابع حذف طلب من طلبات المستخدم
    public function delete($orderId)
    {
     $order = Order::find($orderId);
     if(!$order)
        {
         return response()->json(
            [
                'success'=>false,
                'message'=>'الطلب الذي تريد حذفه غير موجود أنك قمت بحذفه مسبقا'
            ] , 404);
        }
        $order->delete();
        return response()->json(
            [
                'success'=>true,
                'message'=>'تم حذف الطلب بنجاح'
            ] , 200);
    }



    //  تابع يمكن للعميل إلغاء طلبه 
    public function cancel(Request $request , Order $order)
    {
     $user = $request->user();
     if($order->user_id !== $user->id)
        {
         return response()->json(
            [
                'success'=>false,
                'message'=>'ليس لديك صلاحية إلغاء هذا الطلب'
            ] , 403);
        }
        DB::transaction(function() use ($order)
        {
            foreach($order->order_item as $item )
                {
              Product::where('id', $item->product_id)->increment('quantity', $item->quantity);
                }
          $order->update(['status'=>'cancelled']);
        });
        return response()->json([
            'success'=>true,
            'message'=>'تم إلغاء الطلب و زيادة المخزون'
        ] , 200);
        
    }

    
    // دالة مساعدة لترجمة الحالات
    private function getStatusLabel($status)
{
    return [
        'pending'    => 'قيد الانتظار',
        'processing' => 'جاري التجهيز',
        'shipped'    => 'تم الشحن',
        'delivered'  => 'تم التسليم',
        'cancelled'  => 'ملغي'
    ][$status] ?? $status;
}

//    
}
