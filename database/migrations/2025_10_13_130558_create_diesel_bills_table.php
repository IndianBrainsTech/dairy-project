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
        Schema::create('diesel_bills', function (Blueprint $table) {
            $table->id();             
            $table->string('document_number', 15)->nullable();
            $table->date('document_date');
            $table->unsignedBigInteger('bunk_id');
            $table->string('bunk_name', 100);
            $table->string('bill_number', 50)->nullable();
            $table->date('bill_date');
            $table->unsignedBigInteger('route_id');
            $table->string('route_name', 40);
            $table->unsignedBigInteger('vehicle_id');
            $table->string('vehicle_number', 20);
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('driver_name', 50);            
            $table->decimal('fuel', 10, 2);
            $table->decimal('rate', 10, 2);
            $table->decimal('amount', 15, 2);
            $table->unsignedInteger('opening_km');
            $table->unsignedInteger('closing_km');
            $table->unsignedInteger('running_km');
            $table->decimal('kmpl', 8, 2);
            $table->enum('status', ['PENDING', 'ACCEPTED', 'GENERATED'])->default('PENDING');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('actioned_by')->nullable();
            $table->timestamps();
            $table->timestamp('actioned_at')->nullable();

            // Foreign key constraints
            $table->foreign('bunk_id')
                ->references('id')
                ->on('petrol_bunks')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('route_id')
                ->references('id')
                ->on('routes')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('driver_id')
                ->references('id')
                ->on('employees')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('updated_by')
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
        Schema::dropIfExists('diesel_bills');
    }
};
