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
        Schema::create('incentives', function (Blueprint $table) {
            $table->id();
            $table->string('incentive_number',10)->unique();
            $table->date('incentive_date');
            $table->integer('customer_id');
            $table->string('customer_name',50);
            $table->date('from_date');
            $table->date('to_date');
            $table->float('incentive_total',10,2);
            $table->float('leakage_total',10,2);
            $table->float('tds_amount')->nullable();
            $table->float('round_off');
            $table->float('net_amount',10,2);
            $table->enum('incentive_status', ['Pending','Accepted','Cancelled'])->default('Pending');
            $table->enum('payment_status', ['Outstanding','Paid'])->default('Outstanding');
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
        Schema::dropIfExists('incentives');
    }
};
