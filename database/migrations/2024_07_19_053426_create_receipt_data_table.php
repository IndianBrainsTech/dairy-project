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
        Schema::create('receipt_data', function (Blueprint $table) {
            $table->id();
            $table->integer('receipt_id');
            $table->date('receipt_date');
            $table->string('invoice_number',50);
            $table->float('amount');
            $table->enum('receipt_status', ['Outstanding', 'Paid']);
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
        Schema::dropIfExists('receipt_data');
    }
};
