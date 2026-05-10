<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('restrict');
            $table->string('invoice_number'); // رقم فريد للفاتورة
            $table->string('subtotal'); // مجموع عناصر الطلب قبل أي تعديل
            $table->decimal('discount_amount', 10, 2)->default(0); // قيم الخصم
            $table->decimal('tax_amount' , 10 ,2); // قيمة الضريبة المحسوبة 
            $table->string('total_amount'); // التكلفة النهائية  (subtotal - discount + tax + shipping ) 
            $table->decimal('shipping' , 10 ,2) ; // تكلفة الشحن المضافة للفاتورة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
