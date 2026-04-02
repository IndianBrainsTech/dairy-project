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
        Schema::create('followups', function (Blueprint $table) {
            $table->id();
            $table->integer('enquiry_id');
            $table->integer('emp_id');
            $table->string('remarks',500);
            $table->date('next_visit_date')->nullable();
            $table->enum('followup_status', ['First Visit', 'Followup Again', 'Add as Customer', 'Converted as Customer', 'Stop Followup']);
            $table->dateTime('followup_datetime');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
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
        Schema::dropIfExists('followups');
    }
};
