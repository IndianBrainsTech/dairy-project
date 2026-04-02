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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->integer('emp_id');
            $table->date('attn_date');
            $table->enum('attn_session', ['Forenoon', 'Afternoon']);
            $table->time('time_in');
            $table->time('time_out')->nullable();
            $table->double('latitude_in')->nullable();
            $table->double('longitude_in')->nullable();
            $table->double('latitude_out')->nullable();
            $table->double('longitude_out')->nullable();
            $table->string('remarks',100)->nullable();
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
        Schema::dropIfExists('attendances');
    }
};
