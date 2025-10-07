<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    DB::statement("ALTER TABLE shops MODIFY COLUMN status ENUM('pending','active','suspended','closed') DEFAULT 'active'");
}

public function down()
{
    DB::statement("ALTER TABLE shops MODIFY COLUMN status ENUM('active','suspended','closed') DEFAULT 'active'");
}

};
