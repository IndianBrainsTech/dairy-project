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
        Schema::create('stock_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_id'); // FK to stocks.id
            $table->unsignedBigInteger('item_id'); // FK to items.id
            $table->string('item_name', 50);
            $table->string('batch_number', 50)->nullable();
            $table->decimal('quantity', 10, 2);
            $table->unsignedBigInteger('unit_id'); // FK to units.id
            $table->unsignedBigInteger('version_code');
            $table->unsignedBigInteger('user_id'); // FK to users.id
            $table->unsignedBigInteger('record_id');
            $table->enum('action_type', ['CREATE','INSERT', 'UPDATE', 'DELETE']);
            $table->timestamps();

            // Indexes
            $table->index('stock_id');

            // Foreign keys
            $table->foreign('stock_id')->references('id')->on('stocks')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('products')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('unit_id')->references('id')->on('units')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_history');
    }
};
