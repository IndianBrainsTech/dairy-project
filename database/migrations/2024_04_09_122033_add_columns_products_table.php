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
        Schema::table('products', function (Blueprint $table) {
            $table->string('hsn_code',8)->after('snf')->nullable();
            $table->enum('tax_type', ['Taxable', 'Exempted'])->after('hsn_code')->default('Exempted');
            $table->float('gst')->after('tax_type')->nullable();
            $table->float('sgst')->after('gst')->nullable();
            $table->float('cgst')->after('sgst')->nullable();
            $table->float('igst')->after('cgst')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('hsn_code');
            $table->dropColumn('tax_type');
            $table->dropColumn('gst');
            $table->dropColumn('sgst');
            $table->dropColumn('cgst');
            $table->dropColumn('igst');
        });
    }
};
