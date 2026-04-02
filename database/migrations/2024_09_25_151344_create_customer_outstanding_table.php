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
        Schema::create('customer_outstanding', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->string('customer_name',50);
            $table->float('amount',10,2);
            $table->date('txn_date');            
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->enum('receipt_status', ['Outstanding', 'Paid'])->default('Outstanding');
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
        Schema::dropIfExists('customer_outstanding');
    }
};
