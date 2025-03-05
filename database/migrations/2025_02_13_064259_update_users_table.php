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
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('punch_machine_user_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('status_id');
            $table->string('user_name', 250);
            $table->string('password', 250);
            $table->string('password_reset_key', 250)->nullable();
            $table->int('password_reset_date')->nullable();
            $table->string('phone_id', 250)->nullable();
            $table->string('phone_password', 250)->nullable();
            $table->string('first_name', 250)->nullable();
            $table->string('middle_name', 250)->nullable();
            $table->string('last_name', 250)->nullable();
            $table->string('address1', 250)->nullable();
            $table->string('address2', 250)->nullable();
            $table->string('address3', 250);
            $table->string('nic', 12);
            $table->string('city', 250)->nullable();
            $table->string('province', 250)->nullable();
            $table->string('country', 250)->nullable();
            $table->string('postal_code', 250)->nullable();
            $table->string('work_phone', 250)->nullable();
            $table->string('work_phone_ext', 250)->nullable();
            $table->string('home_phone', 250)->nullable();
            $table->string('mobile_phone', 250)->nullable();
            $table->string('immediate_contact_person', 250)->nullable();
            $table->string('immediate_contact_no', 250)->nullable();
            $table->string('fax_phone', 250)->nullable();
            $table->string('home_email', 250)->nullable();
            $table->string('work_email', 250)->nullable();
            $table->string('epf_registration_no', 50);
            $table->string('epf_membership_no', 50);
            $table->int('birth_date')->nullable();
            $table->int('hire_date')->nullable();
            $table->integer('probation');
            $table->integer('basis_of_employment');
            $table->integer('month');
            $table->string('bond_period', 250)->nullable();
            $table->string('sin', 250)->nullable();
            $table->unsignedBigInteger('sex_id')->nullable();
            $table->int('created_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->int('updated_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->int('deleted_date')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->boolean('deleted')->default(0);
            $table->string('ibutton_id', 250)->nullable();
            $table->integer('labor_standard_industry')->default(0);
            $table->unsignedBigInteger('title_id')->nullable();
            $table->string('job_skills', 500);
            $table->unsignedBigInteger('default_branch_id')->nullable();
            $table->unsignedBigInteger('default_department_id')->nullable();
            $table->string('employee_number', 250)->nullable();
            $table->string('employee_number_only', 250)->nullable();
            $table->int('termination_date')->nullable();
            $table->int('resign_date')->nullable();
            $table->text('note')->nullable();
            $table->text('hire_note')->nullable();
            $table->text('termination_note')->nullable();
            $table->string('other_id1', 250)->nullable();
            $table->string('other_id2', 250)->nullable();
            $table->string('other_id3', 250)->nullable();
            $table->string('other_id4', 250)->nullable();
            $table->string('other_id5', 250)->nullable();
            $table->integer('group_id')->default(0);
            $table->text('finger_print_1')->nullable();
            $table->text('finger_print_2')->nullable();
            $table->text('finger_print_3')->nullable();
            $table->text('finger_print_4')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('rf_id')->nullable();
            $table->int('rf_id_updated_date')->nullable();
            $table->int('finger_print_1_updated_date')->nullable();
            $table->int('finger_print_2_updated_date')->nullable();
            $table->int('finger_print_3_updated_date')->nullable();
            $table->int('finger_print_4_updated_date')->nullable();
            $table->string('second_last_name', 250)->nullable();
            $table->decimal('longitude', 15, 10)->nullable();
            $table->decimal('latitude', 15, 10)->nullable();
            $table->string('first_name_metaphone', 250)->nullable();
            $table->string('last_name_metaphone', 250)->nullable();
            $table->int('password_updated_date')->nullable();
            $table->int('last_login_date')->nullable();
            $table->string('full_name', 500);
            $table->string('calling_name', 150);
            $table->string('name_with_initials', 250);
            $table->string('religion', 50);
            $table->unsignedBigInteger('marital_id');
            $table->string('retirement_date', 25);
            $table->string('personal_email', 250);
            $table->string('office_mobile', 20);
            $table->unsignedBigInteger('user_name_title_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
