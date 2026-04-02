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
        Schema::create('incentive_items', function (Blueprint $table) {
            $table->id();
            $table->string('incentive_number',10);
            $table->integer('item_id');
            $table->string('item_name',50);
            $table->float('qty');
            $table->float('inc_rate');
            $table->float('inc_amt');
            $table->float('lkg_qty');
            $table->float('lkg_amt');
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
        Schema::dropIfExists('incentive_items');
    }
};
