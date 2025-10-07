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
        // ⚙️ Thêm giá trị 'rejected' vào ENUM status
        DB::statement("ALTER TABLE shops MODIFY COLUMN status ENUM('pending','active','suspended','rejected','closed') DEFAULT 'pending'");
    }

    public function down(): void
    {
        // 🔙 Quay lại trạng thái cũ (nếu rollback)
        DB::statement("ALTER TABLE shops MODIFY COLUMN status ENUM('pending','active','suspended','closed') DEFAULT 'pending'");
    }
};
