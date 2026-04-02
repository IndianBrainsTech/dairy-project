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
        Schema::create('bulk_milk_orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_num',50)->unique();
            $table->date('invoice_date');
            $table->dateTime('order_dt');
            $table->integer('customer_id');
            $table->string('customer_name',50);
            $table->json('customer_data');
            $table->integer('route_id');
            $table->string('route_name',40);
            $table->integer('vehicle_id')->nullable();
            $table->string('vehicle_num',15);
            $table->integer('driver_id')->nullable();
            $table->string('driver_name',50);
            $table->string('driver_mobile_num',10)->nullable();
            $table->integer('item_count');
            $table->float('tot_amt',10,2);
            $table->float('tcs')->nullable();
            $table->float('round_off');
            $table->float('net_amt',10,2);
            $table->enum('order_status', ['Placed', 'Dispatched', 'Delivered', 'Cancelled'])->default('Placed');
            $table->enum('receipt_status', ['Outstanding', 'Paid'])->default('Outstanding');
            $table->enum('invoice_status', ['Not Generated', 'Generated', 'Cancelled'])->default('Not Generated');
            $table->string('cancel_remarks',300)->nullable();
            $table->string('tally_sync',200)->nullable();
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
        Schema::dropIfExists('bulk_milk_orders');
    }
};
