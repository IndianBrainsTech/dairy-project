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
        Schema::create('gst_master', function (Blueprint $table) {
            $table->id();
            $table->string('hsn_code',8)->unique();
            $table->string('description',50)->nullable();            
            $table->enum('tax_type', ['Taxable', 'Exempted'])->default('Taxable');
            $table->float('gst')->nullable();
            $table->float('sgst')->nullable();
            $table->float('cgst')->nullable();
            $table->float('igst')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gst_master');
    }
};
