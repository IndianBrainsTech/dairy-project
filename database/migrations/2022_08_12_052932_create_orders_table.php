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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_num',50)->unique();
            $table->integer('customer_id');
            $table->integer('area_id');
            $table->integer('route_id');
            $table->integer('user_id');
            $table->enum('order_status', ['Placed', 'Dispatched', 'Delivered', 'Cancelled'])->default('Placed');            
            $table->date('invoice_date');
            $table->dateTime('delivery_dt')->nullable();
            $table->json('address_data');
            $table->float('sales_disc')->nullable();
            $table->float('tax_disc')->nullable();
            $table->float('sales_tcs')->nullable();
            $table->float('tax_tcs')->nullable();
            $table->enum('invoice_status', ['Not Generated', 'Generated', 'Cancelled'])->default('Not Generated');
            $table->string('cancel_remarks',300)->nullable();

            $table->unsignedBigInteger('created_by'); // FK to users.id
            $table->unsignedBigInteger('edited_by')->nullable(); // FK to users.id
            $table->unsignedBigInteger('actioned_by')->nullable(); // FK to users.id

            $table->timestamps();
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('actioned_at')->nullable();

            // Foreign keys
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('edited_by')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('actioned_by')
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
        Schema::dropIfExists('orders');
    }
};
