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
        Schema::create('diesel_bill_statements', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique();
            $table->date('document_date');
            $table->unsignedBigInteger('bunk_id');
            $table->string('bunk_name', 100);
            $table->date('from_date');
            $table->date('to_date');
            $table->json('item_ids');
            $table->integer('item_count');
            $table->decimal('total_fuel', 10, 2);
            $table->decimal('total_running_km', 10, 2);
            $table->decimal('average_kmpl', 10, 2);
            $table->decimal('average_rate', 10, 2);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('tds_amount', 10, 2)->nullable();
            $table->decimal('round_off', 10, 2);
            $table->decimal('net_amount', 12, 2);
            $table->enum('status', ['PENDING', 'ACCEPTED', 'CANCELLED'])->default('PENDING');
            $table->enum('payment_status', ['OUTSTANDING', 'PAID'])->default('OUTSTANDING');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('actioned_by')->nullable();
            $table->timestamps();
            $table->timestamp('actioned_at')->nullable();

            // Foreign key constraints
            $table->foreign('bunk_id')
                ->references('id')
                ->on('petrol_bunks')
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
        Schema::dropIfExists('diesel_bill_statements');
    }
};
