<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 🔹 Trước tiên, đổi các shop có status = 'closed' thành 'rejected' (tránh lỗi khi sửa enum)
       

        // 🔹 Sau đó, sửa lại enum status, loại bỏ 'closed'
        DB::statement("
            ALTER TABLE `shops`
            MODIFY `status` ENUM('pending', 'active', 'suspended', 'rejected')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // 🔹 Nếu rollback, thêm lại giá trị 'closed'
        DB::statement("
            ALTER TABLE `shops`
            MODIFY `status` ENUM('pending', 'active', 'suspended', 'rejected')
            NOT NULL DEFAULT 'pending'
        ");
    }
};
