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
        Schema::create('photo_uploads', function (Blueprint $table) {
            $table->id();
            $table->integer('emp_id');
            $table->enum('tag', ['Enquiry', 'Profile']);
            $table->integer('tag_id');
            $table->string('name',50)->unique()->nullable();
            $table->string('description',300)->nullable();
            $table->dateTime('upload_datetime');
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
        Schema::dropIfExists('photo_uploads');
    }
};
