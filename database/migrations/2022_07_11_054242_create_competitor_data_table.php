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
        Schema::create('competitor_data', function (Blueprint $table) {
            $table->id();
            $table->integer('enquiry_id');
            $table->integer('competitor_id');
            // $table->json('product_data');
            $table->string('product_data',500)->nullable();
            $table->string('offers',100)->nullable();
            $table->string('remarks',300)->nullable();
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
        Schema::dropIfExists('competitor_data');
    }
};
