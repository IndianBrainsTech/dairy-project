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
        Schema::create('order_dispatches', function (Blueprint $table) {
            $table->id();
            $table->date('invoice_date');
            $table->integer('route_id');
            $table->string('route_name',40);
            $table->integer('vehicle_id')->nullable();
            $table->string('vehicle_number',15);
            $table->integer('driver_id')->nullable();
            $table->string('driver_name',50);
            $table->string('mobile_num',10)->nullable();
            $table->json('order_nums');
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
        Schema::dropIfExists('order_dispatches');
    }
};
