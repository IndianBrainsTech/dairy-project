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
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('txn_id',10);
            $table->date('txn_date');
            $table->integer('route_id');
            $table->integer('customer_id');
            $table->string('invoice_num',50);
            $table->enum('invoice_type', ['Sales', 'Tax']);
            $table->json('return_data');
            $table->float('amount',10,2);
            $table->float('tax_amt')->nullable();
            $table->float('total_amt',10,2)->nullable();
            $table->float('round_off',5,2);
            $table->float('net_amt',10,2);
            $table->enum('action', ['Replacement', 'Refund', 'Deduction', 'ReturnOrder']);
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
        Schema::dropIfExists('sales_returns');
    }
};
