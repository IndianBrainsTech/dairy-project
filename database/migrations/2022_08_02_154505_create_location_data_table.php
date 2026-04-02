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
        Schema::create('location_data', function (Blueprint $table) {
            $table->id();
            $table->integer('emp_id');
            $table->double('latitude');
            $table->double('longitude');
            $table->enum('tag', ['Login','Logout','Enquiry','Followup','Route','Order']);
            $table->string('title',100)->nullable();
            $table->string('description',300)->nullable();
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
        Schema::dropIfExists('location_data');
    }
};
