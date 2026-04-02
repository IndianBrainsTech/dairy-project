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
        Schema::create('petrol_bunk_turnover', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bunk_id')
                ->constrained('petrol_bunks')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('reference_date');
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
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
        Schema::dropIfExists('petrol_bunk_turnover');
    }
};
