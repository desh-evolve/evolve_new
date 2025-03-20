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
        Schema::create('user_kpi', function (Blueprint $table) {
            $table->id(); // Auto-incrementing unsigned bigint ID
            $table->integer('user_id'); // User ID
            $table->date('start_date')->nullable(); // Start date
            $table->date('end_date')->nullable(); // End date
            $table->integer('review_date'); // Review date
            $table->integer('default_branch_id'); // Default branch ID
            $table->integer('default_department_id'); // Default department ID
            $table->integer('title_id'); // Title ID
            $table->integer('scorea1'); // Score A1
            $table->text('remarka1')->nullable(); // Remark A1
            $table->integer('scorea2'); // Score A2
            $table->text('remarka2')->nullable(); // Remark A2
            $table->integer('scorea3'); // Score A3
            $table->text('remarka3')->nullable(); // Remark A3
            $table->integer('scorea4'); // Score A4
            $table->text('remarka4')->nullable(); // Remark A4
            $table->integer('scorea5'); // Score A5
            $table->text('remarka5')->nullable(); // Remark A5
            $table->integer('scorea6'); // Score A6
            $table->text('remarka6')->nullable(); // Remark A6
            $table->integer('scorea7'); // Score A7
            $table->text('remarka7')->nullable(); // Remark A7
            $table->integer('scorea8'); // Score A8
            $table->text('remarka8')->nullable(); // Remark A8
            $table->integer('scorea9'); // Score A9
            $table->text('remarka9')->nullable(); // Remark A9
            $table->integer('scorea10'); // Score A10
            $table->text('remarka10')->nullable(); // Remark A10
            $table->integer('scorea11'); // Score A11
            $table->text('remarka11')->nullable(); // Remark A11
            $table->integer('scorea12'); // Score A12
            $table->text('remarka12')->nullable(); // Remark A12
            $table->integer('scoreb1'); // Score B1
            $table->text('remarkb1')->nullable(); // Remark B1
            $table->integer('scoreb2'); // Score B2
            $table->text('remarkb2')->nullable(); // Remark B2
            $table->integer('scoreb3'); // Score B3
            $table->text('remarkb3')->nullable(); // Remark B3
            $table->integer('scoreb4'); // Score B4
            $table->text('remarkb4')->nullable(); // Remark B4
            $table->integer('scoreb5'); // Score B5
            $table->text('remarkb5')->nullable(); // Remark B5
            $table->integer('scoreb6'); // Score B6
            $table->text('remarkb6')->nullable(); // Remark B6
            $table->integer('scorec1'); // Score C1
            $table->text('remarkc1')->nullable(); // Remark C1
            $table->integer('scorec2'); // Score C2
            $table->text('remarkc2')->nullable(); // Remark C2
            $table->integer('scorec3'); // Score C3
            $table->text('remarkc3')->nullable(); // Remark C3
            $table->integer('scorec4'); // Score C4
            $table->text('remarkc4')->nullable(); // Remark C4
            $table->integer('scorec5'); // Score C5
            $table->text('remarkc5')->nullable(); // Remark C5
            $table->integer('scorec6'); // Score C6
            $table->text('remarkc6')->nullable(); // Remark C6
            $table->integer('scored1'); // Score D1
            $table->text('remarkd1')->nullable(); // Remark D1
            $table->integer('scored2'); // Score D2
            $table->text('remarkd2')->nullable(); // Remark D2
            $table->integer('scored3'); // Score D3
            $table->text('remarkd3')->nullable(); // Remark D3
            $table->integer('scored4'); // Score D4
            $table->text('remarkd4')->nullable(); // Remark D4
            $table->integer('scored5'); // Score D5
            $table->text('remarkd5')->nullable(); // Remark D5
            $table->integer('scored6'); // Score D6
            $table->text('remarkd6')->nullable(); // Remark D6
            $table->text('feedback1')->nullable(); // Feedback 1
            $table->text('feedback2')->nullable(); // Feedback 2
            $table->text('feedback3')->nullable(); // Feedback 3
            $table->text('feedback4')->nullable(); // Feedback 4
            $table->text('feedback5')->nullable(); // Feedback 5
            $table->text('feedback6')->nullable(); // Feedback 6
            $table->text('feedback7')->nullable(); // Feedback 7
            $table->text('feedback8')->nullable(); // Feedback 8
            $table->float('total_score_genaral'); // Total general score
            $table->float('avg_key_peformance'); // Average key performance
            $table->string('total_score', 30)->nullable(); // Total score
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->unsignedTinyInteger('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_kpi');
    }
};
