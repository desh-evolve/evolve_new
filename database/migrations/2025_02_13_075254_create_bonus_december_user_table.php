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
        Schema::create('bonus_december_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bonus_december_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('wage');
            $table->integer('service_periods');
            $table->float('kpp_mark');
            $table->float('bonus_amount');
            $table->integer('created_date');
            $table->integer('created_by');
            $table->integer('updated_date');
            $table->integer('updated_by');
            $table->integer('deleted_date');
            $table->integer('deleted_by');
            $table->tinyInteger('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_december_user');
    }
};
