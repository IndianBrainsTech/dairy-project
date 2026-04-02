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
        Schema::create('price_adjustments', function (Blueprint $table) {
            $table->id();
            $table->date('document_date');
            $table->unsignedBigInteger('user_id');
            $table->json('adjustment_data');
            $table->json('masters_list');
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
        Schema::dropIfExists('price_adjustments');
    }
};
