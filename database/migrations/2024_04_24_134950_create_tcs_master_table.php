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
        Schema::create('tcs_master', function (Blueprint $table) {
            $table->id();
            $table->date('effect_date');
            $table->float('tcs_limit',10,2);
            $table->float('with_pan');
            $table->float('without_pan');
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
        Schema::dropIfExists('tcs_master');
    }
};
