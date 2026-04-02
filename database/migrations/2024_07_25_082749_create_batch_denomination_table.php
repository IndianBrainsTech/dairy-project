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
        Schema::create('batch_denomination', function (Blueprint $table) {
            $table->id();
            $table->integer('route_id');
            $table->date('receipt_date');
            $table->json('receipt_numbers');
            $table->float('amount');
            $table->json('denomination');
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
        Schema::dropIfExists('batch_denomination');
    }
};
