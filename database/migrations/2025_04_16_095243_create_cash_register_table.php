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
        Schema::create('cash_register', function (Blueprint $table) {
            $table->id();
            $table->date('record_date');
            $table->float('opening_amount',10,2);
            $table->float('receipt_amount',10,2);
            $table->float('expense_amount',10,2);
            $table->float('closing_amount',10,2);
            $table->json('opening_denomination')->nullable();
            $table->json('receipt_denomination')->nullable();
            $table->json('expense_denomination')->nullable();
            $table->json('closing_denomination')->nullable();
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
        Schema::dropIfExists('cash_register');
    }
};
