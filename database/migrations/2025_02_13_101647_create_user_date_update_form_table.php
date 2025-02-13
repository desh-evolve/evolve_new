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
        Schema::create('user_date_update_form', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('user_id'); // User ID (required)
            $table->string('year_date', 40); // Year and date (required)
            $table->integer('epf_no'); // EPF number (required)
            $table->string('full_name', 120); // Full name (required)
            $table->integer('title_id'); // Title ID (required)
            $table->string('nic', 30); // NIC (required)
            $table->string('contact_home', 30); // Home contact number (required)
            $table->string('contact_mobile', 30); // Mobile contact number (required)
            $table->string('passport_no', 30); // Passport number (required)
            $table->string('driving_licence_no', 20); // Driving license number (required)
            $table->string('permenent_address', 200); // Permanent address (required)
            $table->string('present_address', 200); // Present address (required)
            $table->string('contact_person', 100); // Contact person (required)
            $table->string('address_contact_person', 200); // Contact person's address (required)
            $table->string('tel_contact_person', 20); // Contact person's phone number (required)
            $table->string('maritial_status', 20); // Marital status (required)
            $table->string('spouse_name', 100); // Spouse name (required)
            $table->string('contact_spouse', 20); // Spouse contact number (required)
            $table->string('child1', 200); // Child 1 details (required)
            $table->string('child2', 200); // Child 2 details (required)
            $table->string('child3', 200); // Child 3 details (required)
            $table->string('child4', 200); // Child 4 details (required)
            $table->string('child5', 200); // Child 5 details (required)
            $table->string('child6', 200); // Child 6 details (required)
            $table->integer('created_date')->nullable(); // Created date (nullable)
            $table->integer('created_by')->nullable(); // Created by (nullable)
            $table->integer('updated_date')->nullable(); // Updated date (nullable)
            $table->integer('updated_by')->nullable(); // Updated by (nullable)
            $table->integer('deleted_date')->nullable(); // Deleted date (nullable)
            $table->integer('deleted_by')->nullable(); // Deleted by (nullable)
            $table->tinyInteger('deleted')->default(0); // Deleted flag (default 0)
            $table->text('note')->nullable(); // Additional note (nullable)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_date_update_form');
    }
};
