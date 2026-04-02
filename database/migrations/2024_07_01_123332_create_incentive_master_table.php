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
        Schema::create('incentive_master', function (Blueprint $table) {
            $table->id();
            $table->string('txn_id',7);
            $table->date('txn_date');
            $table->date('effect_date');
            $table->string('narration',200)->nullable();
            $table->json('customer_ids');
            $table->enum('incentive_type', ['Fixed', 'Slab']);
            $table->float('incentive_rate')->nullable();
            $table->json('slab_data')->nullable();
            $table->json('incentive_data');
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
        Schema::dropIfExists('incentive_master');
    }
};
