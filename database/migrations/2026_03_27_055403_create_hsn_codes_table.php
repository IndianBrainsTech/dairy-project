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
        Schema::create('hsn_codes', function (Blueprint $table) {
            $table->id();

            $table->string('hsn_code', 20);
            $table->enum('tax_type', ['TAXABLE', 'EXEMPTED']);

            // Tax percentages (nullable for EXEMPTED if needed)
            $table->decimal('gst', 5, 2)->nullable();
            $table->decimal('sgst', 5, 2)->nullable();
            $table->decimal('cgst', 5, 2)->nullable();
            $table->decimal('igst', 5, 2)->nullable();

            $table->timestamps();

            // Composite unique constraint
            $table->unique(['hsn_code', 'tax_type'], 'hsn_tax_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hsn_codes');
    }
};
