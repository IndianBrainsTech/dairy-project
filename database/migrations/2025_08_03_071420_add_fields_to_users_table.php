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
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_name', 50)->unique()->after('name');
            $table->unsignedBigInteger('role_id')->nullable()->after('user_name');
            $table->string('photo')->nullable()->after('password');
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->after('photo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_name', 'role_id', 'photo', 'status']);
        });
    }
};
