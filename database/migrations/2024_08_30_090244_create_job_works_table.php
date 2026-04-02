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
        Schema::create('job_works', function (Blueprint $table) {
            $table->id();
            $table->string('job_work_num',50)->unique();
            $table->date('job_work_date');
            $table->dateTime('job_work_dt');
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
            $table->float('tot_amt',10,2)->nullable();            
            $table->float('round_off')->nullable();
            $table->float('net_amt',10,2)->nullable();
            $table->enum('job_work_status', ['Placed', 'Dispatched', 'Delivered', 'Cancelled'])->default('Placed');            
            $table->enum('invoice_status', ['Not Generated', 'Generated', 'Cancelled'])->default('Not Generated');
            $table->string('cancel_remarks',300)->nullable();
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
        Schema::dropIfExists('job_works');
    }
};
