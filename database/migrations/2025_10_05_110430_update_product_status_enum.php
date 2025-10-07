<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ⚠️ Trước tiên, xoá CHECK constraint cũ (nếu MySQL 8+)
        // và thay lại enum giá trị mới
        DB::statement("
            ALTER TABLE products 
            MODIFY COLUMN status 
            ENUM('pending', 'in_stock', 'out_of_stock', 'rejected') 
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // Quay lại enum cũ nếu rollback
        DB::statement("
            ALTER TABLE products 
            MODIFY COLUMN status 
            ENUM('pending', 'in_stock', 'out_of_stock', 'discontinued') 
            NOT NULL DEFAULT 'pending'
        ");
    }
};
