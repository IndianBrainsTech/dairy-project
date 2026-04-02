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
        Schema::create('stock_register', function (Blueprint $table) {
            $table->id();
            $table->date('record_date');
            $table->unsignedBigInteger('item_id');
            $table->string('item_name', 50);
            $table->unsignedBigInteger('unit_id');
            $table->decimal('opening_qty', 15, 2)->default(0);
            $table->decimal('production_qty', 15, 2)->default(0);
            $table->decimal('sales_qty', 15, 2)->default(0);
            $table->decimal('return_qty', 15, 2)->default(0);
            $table->decimal('closing_qty', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['item_id', 'record_date']);
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
        Schema::dropIfExists('stock_register');
    }
};
