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
        Schema::create('incentive_payouts', function (Blueprint $table) {
            $table->id();
            $table->string('incentive_number',10);
            $table->integer('customer_id');
            $table->date('document_date');
            $table->float('amount',10,2);
            $table->enum('payout_mode', ['Receipt','Bank']);
            $table->string('reference_number',10)->nullable();
            $table->enum('payout_status', ['Pending','Paused','Approved','Cancelled'])->default('Pending');
            $table->date('approval_date')->nullable();
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
        Schema::dropIfExists('incentive_payouts');
    }
};
