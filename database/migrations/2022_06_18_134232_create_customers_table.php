<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('customer_name',50)->unique();
            $table->string('customer_code',20)->unique()->nullable();
            $table->enum('group', ['Retailer', 'Distributor', 'Outlet', 'Company', 'Function']);
            $table->integer('route_id');
            $table->integer('area_id');
            $table->string('address_lines',500);
            $table->string('district',50);
            $table->string('state',50);
            $table->string('landmark',100)->nullable();
            $table->string('pincode',6)->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->string('contact_num',15);
            $table->string('contact_name',50)->nullable();
            $table->string('alternate_num',15)->nullable();
            $table->string('alternate_name',50)->nullable();
            $table->string('email_id',50)->nullable();
            $table->integer('staff_id');
            $table->string('remarks',500)->nullable();

            $table->string('billing_name',50);
            $table->float('credit_limit')->nullable();
            $table->enum('gst_type', ['Interstate Registered', 'Interstate Unregistered', 'Intrastate Registered', 'Intrastate Unregistered']);
            $table->string('gst_number',15)->nullable();
            $table->string('pan_number',10)->nullable();
            $table->float('outstanding')->nullable();
            $table->enum('incentive_mode', ['Daily', 'Weekly', 'Twice Monthly', 'Monthly'])->nullable();
            $table->enum('payment_mode', ['Cash & Carry', 'Bill to Bill', 'Weekly', 'Twice Monthly', 'Monthly'])->nullable();
            $table->enum('tcs_status', ['TCS Not Applicable', 'TCS Applicable', 'TCS Applied'])->nullable();
            $table->enum('tds_status', ['TDS Not Applicable', 'TDS Applicable', 'TDS Applied'])->nullable();            
            $table->boolean('link_customer');
            $table->integer('link_cust_id')->nullable();
            $table->date('customer_since')->nullable();

            $table->string('owner_name',50)->nullable();
            $table->enum('gender', ['Male','Female'])->nullable();
            $table->date('dob')->nullable();
            $table->string('aadhaar',16)->nullable();
            $table->string('profile_image',10)->nullable();
            $table->string('shop_photo',10)->nullable();

            $table->string('bank_name',50)->nullable();
            $table->string('branch',50)->nullable();
            $table->string('ifsc',11)->nullable();
            $table->string('acc_holder',50)->nullable();
            $table->string('acc_number',20)->nullable();

            $table->string('tally_sync',200)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->tinyInteger('payment_mode_order')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
