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
        Schema::create('current_stocks', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('item_id')->unique();
            $table->string('item_name', 50);
            $table->unsignedBigInteger('unit_id');
            $table->decimal('opening_qty', 15, 2)->default(0);
            $table->decimal('production_qty', 15, 2)->default(0);
            $table->decimal('sales_qty', 15, 2)->default(0);
            $table->decimal('return_qty', 15, 2)->default(0);
            $table->decimal('current_stock', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('current_stocks');
    }
};
