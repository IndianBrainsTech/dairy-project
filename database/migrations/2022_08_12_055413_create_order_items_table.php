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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->string('order_num',50);
            $table->enum('item_category', ['Regular','Damage','Spoilage','Sample'])->default('Regular');
            $table->integer('product_id');
            $table->string('product_name',50);
            $table->float('qty');
            $table->string('unit_name',20);
            $table->integer('unit_id');
            $table->string('qty_str',20);
            $table->string('price_str',20);
            $table->float('amount');
            $table->float('tax')->nullable();
            $table->float('total');
            $table->float('discount')->nullable();
            $table->boolean('taxable');
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
        Schema::dropIfExists('order_items');
    }
};
