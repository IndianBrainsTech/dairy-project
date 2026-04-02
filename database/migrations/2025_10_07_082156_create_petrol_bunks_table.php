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
        Schema::create('petrol_bunks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 10)->unique();
            $table->text('address');
            $table->string('pin_code', 6)->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('pan', 10)->nullable();
            $table->string('gst_number', 15)->nullable();
            $table->enum('tds_status', ['NOT_APPLICABLE','APPLICABLE','APPLIED']);
            $table->unsignedBigInteger('bank_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('account_holder', 100);
            $table->string('account_number', 30);
            $table->enum('status', ['ACTIVE','INACTIVE'])->default('ACTIVE');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('bank_id')
                ->references('id')
                ->on('banks')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('bank_branches')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('petrol_bunks');
    }
};
