<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // إنشاء فاتوة جديدة , أدمن فقط 
public function generate(Request $request , Order $orderId)
{
$user = $request->user();
if($user->role !=='admin')
    {
    return response()->json([
        'success'=>false,
        'message'=>'غير مصرح لك بإنشاء فواتير'
    ] , 403);
    }
// منع  إنشاء الفاتورة مرة ثانية لنفس الطلب
if($orderId->invoice)
    {
   return response()->json([
    'success'=>'تم استرجاع الفاتورة الموجودة  مسبقا ',
    'data'=>$orderId->invoice
   ] , 200);
    }
 // 💰 حساب القيم المالية (يمكن تعديل نسبة الضريبة حسب سياسة متجرك)
    $subtotal = $orderId->total_price; // مجموع العناصر
    $taxRate = 0.15; // 15% قيمة الضريبة مثلا 
    $taxAmount = $subtotal * $taxRate;
    $discount = 0; // قيمة للحسم إن وجدت
    $shippingCost = $orderId->shipment?->status === 'delivered' ? 50.00 : 0.00; //  شحن مجاني إذا لم يُشحن بعد
    $totalAmount = $subtotal - $discount + $taxAmount + $shippingCost; // القيمة النهائية

    //  توليد رقم فاتورة فريد //  INV-2026-0013 مثلا
    $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($orderId->id, 4, '0', STR_PAD_LEFT);

    //  إنشاء الفاتورة 
        $invoice = DB::transaction(function () use ($orderId, $invoiceNumber, $subtotal, $discount, $taxAmount, $shippingCost, $totalAmount) {
        return Invoice::create([
            'order_id'       => $orderId->id,
            'invoice_number' => $invoiceNumber,
            'subtotal'       => (string) $subtotal,          
            'discount_amount'=> $discount,
            'tax_amount'     => $taxAmount,
            'total_amount'   => (string) $totalAmount,  
            'shipping'       => $shippingCost
        ]);
    });

    return response()->json([
        'success' => true,
        'message' => 'تم إنشاء الفاتورة بنجاح',
        'data'    => $invoice
    ], 201);

}




// عرض فاتورة جديدة , لمالك الفاتورة أو الأدمن 
public function show(Request $request , Invoice $invoiceId)
{
  $user = $request->user();
  if($invoiceId->order->user_id !== $user->id   && $user->role !=='admin')
    {
     return response()->json(
        [
            'success'=>false,
            'message'=>'غير مصرح لك بعرض هذه الفاتورة'
        ] , 403);
    }
//  تحميل البيانات
 $invoiceId->load([
    'order.order_item.product:id,name',
    'order.shipment:id,address,city,status',
    'order.payment:id,payment_method,transaction_id'
 ]);

 $data = [
        'invoice_number' => $invoiceId->invoice_number,
        'issued_at'      => $invoiceId->created_at->format('Y-m-d H:i:s'),
        'order_info'     => [
            'order_id'   => $invoiceId->order_id,
            'order_date' => $invoiceId->order->order_date,
            'status'     => $invoiceId->order->status
        ],
        'customer'       => [
            'name'  => $invoiceId->order->user->name,
            'email' => $invoiceId->order->user->email,
            'phone' => $invoiceId->order->user->phone ?? 'غير متاح'
        ],
        'shipping_info'  => $invoiceId->order->shipment ? [
            'address' => $invoiceId->order->shipment->address,
            'city'    => $invoiceId->order->shipment->city,
            'status'  => $invoiceId->order->shipment->status
        ] : null,
        'order_item' => $invoiceId->order->order_item->map(fn($item) => [
            'product_name' => $item->product->name,
            'quantity'     => $item->quantity,
            'unit_price'   => number_format($item->unit_price, 2),
            'subtotal'     => number_format($item->quantity * $item->unit_price, 2)
        ]),
        'financials' => [
            'subtotal'        => $invoiceId->subtotal,
            'discount_amount' => number_format($invoiceId->discount_amount, 2),
            'tax_amount'      => number_format($invoiceId->tax_amount, 2),
            'shipping'        => number_format($invoiceId->shipping, 2),
            'total_amount'    => $invoiceId->total_amount,
            'currency'        => 'USD' // أو من إعدادات المتجر
        ],
        'payment_reference' => $invoiceId->order->payment ? [
            'method'         => $invoiceId->order->payment->payment_method,
            'transaction_id' => $invoiceId->order->payment->transaction_id
        ] : null
    ];

    return response()->json([
        'success' => true,
        'message' => 'تم جلب الفاتورة بنجاح',
        'data'    => $data
    ]);
}





// عرض جميع الفواتير مع فلاتر , للأدمن فقط
public function index(Request $request , )
{
 if ($request->user()->role !== 'admin') {
        return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
    }

    //  بناء الاستعلام مع العلاقات
    $query = Invoice::with([
        'order.user:id,first_name,last_name,email',
        'order:id,user_id,total_price,status'
    ]);

    // فلاتر اختيارية أي يقوم الأدمن بالبحث حسب الحقل الذي يريده
    if ($request->filled('order_id')) {
        $query->where('order_id', $request->order_id);
    }
    if ($request->filled('invoice_number')) {
        $query->where('invoice_number', 'like', "%{$request->invoice_number}%");
    }
    if ($request->filled('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }

    $invoices = $query->latest()->paginate(5);  // اي عرض كل خمسة فواتير في صفحة

    //  تنسيق للعرض
    $formatted = $invoices->map(fn($inv) => [
        'id'             => $inv->id,
        'invoice_number' => $inv->invoice_number,
        'order_id'       => $inv->order_id,
        'customer_first_name'  => $inv->order->user->first_name ?? 'N/A',
        'customer_last_name'  => $inv->order->user->last_name ?? 'N/A',
        'total_amount'   => $inv->total_amount,
        'created_at'     => $inv->created_at->format('Y-m-d H:i:s')
    ]);

    return response()->json([
        'success'    => true,
        'message'    => 'تم جلب قائمة الفواتير',
        'data'       => $formatted,
        'pagination' => [
            'total' => $invoices->total(),
            'page'  => $invoices->currentPage(),
            'pages' => $invoices->lastPage()
        ]
    ]);

}






// تحميل الفاتورة بصيغة pdf
// مثل فكرة تطبيق شام كاش عندما نقوم بتحويل العمولة
public function download()
{

}
}
