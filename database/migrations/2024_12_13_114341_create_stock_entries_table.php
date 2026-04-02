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
        Schema::create('stock_entries', function (Blueprint $table) {
            $table->id();  
            $table->string('txn_id');          
            $table->string('product_name');
            $table->integer('product_id');   
            $table->integer('group_id');             
            $table->float('entry_qty',10,2);   
            $table->string('entry_unit');           
            $table->float('primary_unit_qty',10,2);
            $table->string('primary_unit'); 
            $table->float('total_stock_qty',10,2);           
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
        Schema::dropIfExists('stock_entries');
    }
};
