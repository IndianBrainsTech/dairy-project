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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('name',50);
            $table->string('code',10)->unique();
            $table->integer('role_id');
            $table->integer('manager_id');
            $table->date('dob')->nullable();
            $table->enum('gender', ['Male','Female'])->nullable();
            $table->string('user_name',20)->unique();
            $table->string('password');
            $table->string('photo',10)->nullable();
            $table->string('remarks',500)->nullable();

            $table->string('address',500);
            $table->string('district',50);
            $table->string('state',50);
            $table->string('landmark',100)->nullable();
            $table->string('pincode',6)->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->string('mobile_num',15)->unique();
            $table->string('alternate_num',15)->nullable();
            $table->string('email_id',50)->nullable();

            $table->string('father_name',50)->nullable();
            $table->string('aadhaar_num',16)->nullable();
            $table->string('license_num',16)->nullable();
            $table->date('license_validity')->nullable();
            $table->enum('blood_group', ['O+','O-','A+','A-','B+','B-','AB+','AB-'])->nullable();
            $table->date('doj')->nullable();

            $table->string('bank_name',50)->nullable();
            $table->string('branch',50)->nullable();
            $table->string('ifsc',11)->nullable();
            $table->string('acc_holder',50)->nullable();
            $table->string('acc_number',20)->nullable();
 
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
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
        Schema::dropIfExists('employees');
    }
};
