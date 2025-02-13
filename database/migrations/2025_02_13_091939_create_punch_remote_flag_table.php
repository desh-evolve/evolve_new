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
        Schema::create('punch_remote_flag', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->integer('local_server_punch_id')->nullable(); // Foreign key to `local_server_punch`
            $table->integer('local_server_employee_id'); // Foreign key to `employee`
            $table->integer('remote_server_user_id'); // Foreign key to `remote_server_user`
            $table->dateTime('punch_time'); // Date and time of the punch
            $table->tinyInteger('in_and_out'); // In/Out flag (1 = in, 0 = out)
            $table->tinyInteger('flag')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('punch_remote_flag');
    }
};
