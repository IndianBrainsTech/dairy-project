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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name',50)->unique();
            $table->string('short_name',15)->unique();
            $table->string('item_code',10)->nullable()->unique();
            $table->integer('group_id');
            $table->enum('type', ['Pouch', 'Product'])->default('Pouch');
            $table->string('description',300)->nullable();
            $table->integer('display_index')->nullable();
            $table->float('mrp');
            $table->float('fat')->nullable();
            $table->float('snf')->nullable();
            $table->string('image',10)->nullable();
            $table->boolean('visible_app');
            $table->boolean('visible_invoice');
            $table->boolean('visible_bulkmilk');
            $table->string('tally_sync',200)->nullable();
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
        Schema::dropIfExists('products');
    }
};
