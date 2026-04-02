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
        Schema::create('date_entry_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module', 50);
            $table->string('tag', 50);
            $table->unsignedSmallInteger('days_before')->default(0);
            $table->unsignedSmallInteger('days_after')->default(0);            
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
        Schema::dropIfExists('date_entry_setting');
    }
};
