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
        Schema::create('diesel_bill_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('statement_id');
            $table->date('request_date');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['PENDING', 'PAUSED', 'APPROVED', 'CANCELLED'])->default('PENDING');
            $table->string('reference_number', 15)->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('actioned_by')->nullable();
            $table->timestamps();
            $table->timestamp('actioned_at')->nullable();

            // Foreign key constraints
            $table->foreign('statement_id')
                ->references('id')
                ->on('diesel_bill_statements')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('actioned_by')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('diesel_bill_payments');
    }
};
