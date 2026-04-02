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
        Schema::create('bank_branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_id');
            $table->string('name', 50);
            $table->string('ifsc', 20)->unique();
            $table->timestamps();

            $table->foreign('bank_id')
                ->references('id')
                ->on('banks')
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // Prevents deleting a bank if branches exist

            // Make branch name unique within the same bank
            $table->unique(['bank_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_branches');
    }
};
