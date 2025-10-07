<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id'); // shop_id là user_id của shop
            $table->string('code')->unique();
            $table->integer('discount_amount')->default(0);
            $table->date('expiry_date');
            $table->enum('status', ['active', 'expired'])->default('active');
            $table->timestamps();

            // Khóa ngoại tới bảng shops
            $table->foreign('shop_id')->references('user_id')->on('shops')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
