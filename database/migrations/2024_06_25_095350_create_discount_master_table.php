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
        Schema::create('discount_master', function (Blueprint $table) {
            $table->id();
            $table->string('txn_id',7);
            $table->date('txn_date');
            $table->date('effect_date');
            $table->string('narration',200)->nullable();
            $table->json('customer_ids');
            $table->json('discount_list');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
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
        Schema::dropIfExists('discount_master');
    }
};
