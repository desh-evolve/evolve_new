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
        Schema::create('user_job_history', function (Blueprint $table) {
            $table->id(); // Auto-incrementing unsigned bigint ID
            $table->integer('user_id'); // User ID
            $table->integer('default_branch_id'); // Default branch ID
            $table->integer('default_department_id'); // Default department ID
            $table->integer('title_id'); // Title ID
            $table->date('first_worked_date')->nullable(); // First worked date
            $table->date('last_worked_date')->nullable(); // Last worked date
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->text('note')->nullable(); // Note
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_job_history');
    }
};
