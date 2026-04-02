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
        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('role_name',50)->unique();
            $table->string('short_name',50)->unique()->nullable();
            $table->enum('role_nature', ['Admin', 'Manager', 'Field', 'Office']);
            $table->enum('department', ['Admin', 'Sales', 'Procurement','Transport']);
            $table->string('reporting_roles',50)->nullable();
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
        Schema::dropIfExists('designations');
    }
};
