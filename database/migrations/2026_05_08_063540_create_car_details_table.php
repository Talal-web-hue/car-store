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
        Schema::create('car_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('brand'); // ماركة السيارة
            $table->string('model'); // موديل السيارة
            $table->integer('year'); // سنة الصنع
            $table->string('engine_type'); // نوع المحرك    
            $table->string('plate_number'); // رقم اللوحة
            $table->string('color'); // لون السيارة
            $table->string('type'); // نوع السيارة (مثلاً: سيدان، SUV، هاتشباك)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_details');
    }
};
