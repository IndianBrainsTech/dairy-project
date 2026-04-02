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
        Schema::create('bank_account', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name',50);
            $table->string('acc_holder',50);
            $table->string('acc_number',20);
            $table->string('ifsc',11);
            $table->string('branch',50);
            $table->string('display_name',50)->unique('display_name_unique');
            $table->timestamps();
            $table->unique(['ifsc','acc_number'],'acc_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_account');
    }
};
