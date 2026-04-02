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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_num',10)->unique();
            $table->date('receipt_date');
            $table->integer('route_id');
            $table->integer('customer_id');
            $table->string('customer_name',50);            
            $table->float('amount');
            $table->enum('mode', ['Cash', 'Bank', 'Incentive', 'Deposit']);
            $table->json('receipt_data');
            $table->json('denomination')->nullable();
            $table->integer('bank_id')->nullable();
            $table->string('trans_num',30)->nullable();
            $table->string('remarks',300)->nullable();
            $table->json('incentive_data')->nullable();
            $table->float('aggregate_amt');
            $table->float('advance_amt')->nullable();
            $table->float('excess_amt')->nullable();
            $table->enum('status', ['Pending', 'Approved'])->default('Pending');
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
        Schema::dropIfExists('receipts');
    }
};
