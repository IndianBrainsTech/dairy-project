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
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name',50);
            $table->enum('shop_type', ['Retailer', 'Distributor', 'Outlet']);
            $table->integer('area_id');
            $table->string('area_name',50);
            $table->string('address',500);
            $table->string('landmark',100)->nullable();
            $table->string('remarks',500)->nullable();
            $table->date('followup_date')->nullable();
            $table->string('contact_num',15)->nullable();
            $table->string('contact_name',50)->nullable();
            $table->string('alternate_num',15)->nullable();
            $table->string('alternate_name',50)->nullable();
            $table->dateTime('enq_datetime')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->integer('emp_id');
            $table->enum('conversion_status', ['Just Enquired', 'Followup in Progress', 'Conversion Request in Progress', 'Converted as Customer', 'Followup Stopped'])->default('Just Enquired');
            $table->integer('customer_id')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
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
        Schema::dropIfExists('enquiries');
    }
};
