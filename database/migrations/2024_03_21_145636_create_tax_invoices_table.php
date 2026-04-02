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
        Schema::create('tax_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_num',50)->unique();
            $table->date('invoice_date');
            $table->string('order_num',50)->unique();
            $table->dateTime('order_dt');
            $table->integer('customer_id');
            $table->string('customer_name',50);
            $table->string('mobile_num',15);
            $table->string('gst_number',15)->nullable();            
            $table->integer('route_id');
            $table->string('route_name',40);
            $table->string('vehicle_num',15);
            $table->string('driver_name',50);            
            $table->integer('item_count');
            $table->float('crates');
            $table->float('qty');
            $table->float('amount',10,2);
            $table->float('tax_amt');
            $table->float('tot_amt',10,2);
            $table->float('tcs')->nullable();
            $table->float('discount')->nullable();
            $table->float('round_off');
            $table->float('net_amt',10,2);
            $table->enum('receipt_status', ['Outstanding', 'Paid'])->default('Outstanding');
            $table->enum('invoice_status', ['Generated', 'Cancelled'])->default('Generated');
            $table->string('cancel_remarks',300)->nullable();
            $table->float('last_in_amount')->nullable();
            $table->float('empty_crates_received')->nullable();
            $table->float('amount_received')->nullable();
            $table->float('last_receipt')->nullable();
            $table->float('last_crates_received')->nullable();
            $table->unsignedBigInteger('ordered_by')->nullable();
            $table->boolean('is_printed')->default(false);
            $table->string('tally_sync',200)->nullable();
            $table->timestamps();

            $table->foreign('ordered_by')
                ->references('id')->on('users')
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
        Schema::dropIfExists('tax_invoices');
    }
};
