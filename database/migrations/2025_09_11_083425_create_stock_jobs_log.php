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
        Schema::create('stock_jobs_log', function (Blueprint $table) {
            $table->id();
            $table->date('job_date'); // which date the job processed
            $table->string('status'); // success | failed
            $table->unsignedInteger('processed_count')->default(0); // number of items processed
            $table->text('message')->nullable(); // error or success details
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
        Schema::dropIfExists('stock_jobs_log');
    }
};
