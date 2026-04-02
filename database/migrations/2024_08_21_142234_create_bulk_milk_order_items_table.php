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
        Schema::create('bulk_milk_order_items', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_num',50);
            $table->integer('product_id');
            $table->string('product_name',80);
            $table->string('hsn_code',8);
            $table->float('qty_kg',10,2);
            $table->float('clr');
            $table->float('fat')->nullable();
            $table->float('snf');
            $table->float('qty_ltr',10,2);
            $table->float('ts',10,3);
            $table->float('ts_rate');
            $table->float('rate');
            $table->float('amount',12,2);
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
        Schema::dropIfExists('bulk_milk_order_items');
    }
};
