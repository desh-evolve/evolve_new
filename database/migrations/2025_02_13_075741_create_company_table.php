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
        Schema::create('company', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('product_edition_id');
            $table->string('name', 250)->nullable();
            $table->string('short_name', 15)->nullable();
            $table->string('address1', 250)->nullable();
            $table->string('address2', 250)->nullable();
            $table->string('city', 250)->nullable();
            $table->string('province', 250)->nullable();
            $table->string('country', 250)->nullable();
            $table->string('postal_code', 250)->nullable();
            $table->string('work_phone', 250)->nullable();
            $table->string('fax_phone', 250)->nullable();
            $table->string('business_number', 250)->nullable();
            $table->unsignedInteger('epf_number');
            $table->string('originator_id', 250)->nullable();
            $table->string('data_center_id', 250)->nullable();
            $table->unsignedBigInteger('admin_contact')->nullable();
            $table->unsignedBigInteger('billing_contact')->nullable();
            $table->unsignedBigInteger('support_contact')->nullable();
            $table->integer('created_date')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_date')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->tinyInteger('deleted')->default(0);
            $table->tinyInteger('enable_second_last_name')->default(0);
            $table->smallInteger('ldap_authentication_type_id')->default(0);
            $table->string('ldap_host', 100)->nullable();
            $table->integer('ldap_port')->default(389);
            $table->string('ldap_bind_user_name', 100)->nullable();
            $table->string('ldap_bind_password', 100)->nullable();
            $table->string('ldap_base_dn', 250)->nullable();
            $table->string('ldap_bind_attribute', 100)->nullable();
            $table->string('ldap_user_filter', 250)->nullable();
            $table->string('ldap_login_attribute', 100)->nullable();
            $table->string('ldap_group_dn', 250)->nullable();
            $table->string('ldap_group_user_attribute', 100)->nullable();
            $table->string('ldap_group_name', 100)->nullable();
            $table->string('ldap_group_attribute', 250)->nullable();
            $table->unsignedBigInteger('industry_id')->default(0);
            $table->smallInteger('password_policy_type_id')->default(0);
            $table->smallInteger('password_minimum_permission_level')->default(10);
            $table->smallInteger('password_minimum_strength')->default(3);
            $table->smallInteger('password_minimum_length')->default(8);
            $table->smallInteger('password_minimum_age')->default(0);
            $table->smallInteger('password_maximum_age')->default(365);
            $table->string('name_metaphone', 250)->nullable();
            $table->decimal('longitude', 15, 10)->nullable();
            $table->decimal('latitude', 15, 10)->nullable();
            $table->string('other_id1', 250)->nullable();
            $table->string('other_id2', 250)->nullable();
            $table->string('other_id3', 250)->nullable();
            $table->string('other_id4', 250)->nullable();
            $table->string('other_id5', 250)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company');
    }
};
