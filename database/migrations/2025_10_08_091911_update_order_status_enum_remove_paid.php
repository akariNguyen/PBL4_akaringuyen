<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ⚙️ Xóa giá trị 'paid' khỏi ENUM status của bảng orders
        DB::statement("
            ALTER TABLE orders 
            MODIFY COLUMN status 
            ENUM('pending', 'shipped', 'completed', 'cancelled')
            DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // 🔁 Khôi phục lại nếu rollback
        DB::statement("
            ALTER TABLE orders 
            MODIFY COLUMN status 
            ENUM('pending', 'paid', 'shipped', 'completed', 'cancelled')
            DEFAULT 'pending'
        ");
    }
};
