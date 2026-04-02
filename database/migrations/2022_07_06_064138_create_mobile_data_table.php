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
        Schema::create('mobile_data', function (Blueprint $table) {
            $table->id();
            $table->string('user_id',10);
            $table->string('mobile_num',15);
            $table->string('app_version',10);
            $table->string('model',30);
            $table->string('android_version',20);
            $table->string('unique_code',100)->nullable();
            $table->string('otp',4)->default('1010');
            $table->boolean('otp_verified')->default('0');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mobile_data');
    }
};
