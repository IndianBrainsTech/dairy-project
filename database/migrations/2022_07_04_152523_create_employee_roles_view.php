<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "CREATE VIEW employee_roles_view as 
                SELECT employees.id as emp_id,employees.name as emp_name,employees.code as emp_code,employees.role_id as role_id,
                       designations.role_name as role_name,designations.short_name as short_name,designations.role_nature as role_nature,designations.department as department
                from employees,designations
                WHERE employees.role_id=designations.id
                ORDER BY employees.id"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW employee_roles_view");
    }
};
