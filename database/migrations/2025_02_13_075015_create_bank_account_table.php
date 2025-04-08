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
        Schema::create('bank_account', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('institution', 15);
            $table->string('transit', 15);
            $table->string('account', 50);
            $table->string('bank_name', 244);
            $table->string('bank_branch', 244);
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
            $table->string('branch_code', 15)->nullable();
            $table->string('transit_two', 15)->nullable();
            $table->string('bank_two_name', 250)->nullable();
            $table->string('bank_two_branch', 250)->nullable();
            $table->string('branch_two_code', 15)->nullable();
            $table->string('account_two', 50)->nullable();
            $table->decimal('amount_bank_two', 20, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_account');
    }
};
