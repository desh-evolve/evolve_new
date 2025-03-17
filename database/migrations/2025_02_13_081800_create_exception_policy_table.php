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
        Schema::create('exception_policy', function (Blueprint $table) {
            $table->id();
            $table->integer('exception_policy_control_id');
            $table->string('type_id', 3);
            $table->integer('severity_id');
            $table->integer('grace')->nullable();
            $table->integer('watch_window')->nullable();
            $table->integer('demerit')->nullable();
            $table->boolean('active')->default(0);
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->boolean('enable_authorization')->default(0);
            $table->integer('email_notification_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exception_policy');
    }
};
