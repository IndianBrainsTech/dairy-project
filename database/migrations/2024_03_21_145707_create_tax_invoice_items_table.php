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
        Schema::create('tax_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_num',50);
            $table->integer('product_id');
            $table->string('product_name',80);
            $table->enum('item_category', ['Regular','Damage','Spoilage','Sample'])->default('Regular');
            $table->string('hsn_code',8);
            $table->float('crates')->nullable();
            $table->float('qty');
            $table->float('amount');
            $table->float('tax_amt')->nullable();
            $table->float('tot_amt');
            $table->float('gst');
            $table->float('sgst')->nullable();
            $table->float('cgst')->nullable();
            $table->float('igst')->nullable();
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
        Schema::dropIfExists('tax_invoice_items');
    }
};
