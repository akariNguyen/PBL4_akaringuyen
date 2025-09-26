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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // liên kết sản phẩm
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            // liên kết người dùng (người đánh giá)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // số sao (1-5)
            $table->tinyInteger('rating')->default(5);

            // nội dung bình luận
            $table->text('comment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
